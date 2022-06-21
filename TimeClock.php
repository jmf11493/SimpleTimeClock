<?php
require_once('User.php');
require_once('UserMemoryDatabase.php');

class TimeClock
{

    private $database;

    // Message prompts
    private $enterEmployeeIdMessage = "\n\nEnter your employee id to ";
    private $clockInMessage = "clock in.(Integer/QUIT)\n";
    private $clockOutMessage = "clock out.(Integer/QUIT)\n";
    private $takeBreakMessage = "take break.(Integer/QUIT)\n";
    private $endBreakMessage = "end break.(Integer/QUIT)\n";
    private $takeLunchMessage = "take lunch.(Integer/QUIT)\n";
    private $endLunchMessage = "end lunch.(Integer/QUIT)\n";
    private $reportOnEmployeeMessage = "\n\nEnter an employee id to report on.(Integer/QUIT)\n";
    private $reportForEmployeeNameMessage = "\nReport for employee: ";
    private $reportForEmployeeIdMessage = " Employee ID: ";
    private $reportForEmployeeAdminMessage = " Is Admin?: ";
    private $enterNewEmployeeIdMessage = "Please enter a unique employee id.(Integer/QUIT)\n";
    private $enterEmployeeNameMessage = "Please enter an employee name.(Text/QUIT)\n";
    private $enterEmployeeAdminMessage = "Is this user an admin?(y/n/QUIT)\n";
    // Success messages
    private $clockInSuccessMessage = "You have successfully clocked in.\n\n";
    private $clockOutSuccessMessage = "You have successfully clocked out.\n\n";
    private $breakStartSuccessMessage = "You are now on break.\n\n";
    private $breakEndSuccessMessage = "You are no longer on break.\n\n";
    private $lunchStartSuccessMessage = "You are now on a lunch break.\n\n";
    private $lunchEndSuccessMessage = "You are no longer on a lunch break.\n\n";
    // Error messages
    private $invalidSelectionMessage = "Invalid selection, try again.\n\n";
    private $invalidInputMessage = "Invalid input, try again.\n\n";
    private $userNotClockedInFailureMessage = "You are not clocked in!\n\n";
    private $clockInFailureMessage = "You are already clocked in!\n\n";
    private $breakStartFailureMessage = "You are already on break!\n\n";
    private $breakEndFailureMessage = "You do not have an active break!\n\n";
    private $lunchStartFailureMessage = "You are already on a lunch break!\n\n";
    private $lunchEndFailureMessage = "You do not have an active lunch break!\n\n";
    private $activeBreakFailureMessage = "You have an active break, please end your break before clocking out.\n\n";
    private $userNotInSystemFailureMessage = "User id is not in the system, please try again.\n\n";
    private $userAlreadyExistsFailureMessage = "Employee id already exists, please try again.\n\n";

    public function __construct() {
        $this->database = new UserMemoryDatabase();
    }

    private function displayOptions() {
        echo("\nenter 1 to clock in\n");
        echo("enter 2 to clock out\n");
        echo("enter 3 to go on break\n");
        echo("enter 4 to return from break\n");
        echo("enter 5 to go on lunch\n");
        echo("enter 6 to return from lunch\n");
        echo("enter 7 to register new user\n");
        echo("enter 8 to see all users\n");
        echo("enter 9 to run reports\n");
        echo("enter 10 to run filtered report\n");
        echo("enter 0 to exit\n");
    }

    public function startApplication() {
        $stdin = fopen('php://stdin', 'r');
        $runApplication = true;
        echo("\nWelcome to TimeClock App\n");
        while($runApplication) {
            $this->displayOptions();
            fscanf(STDIN, "%d\n", $number);
            if (is_int($number)) {
                switch ($number) {
                    case 1:
                        $this->clockIn();
                        break;
                    case 2:
                        $this->clockOut();
                        break;
                    case 3:
                        $this->takeBreak();
                        break;
                    case 4:
                        $this->endBreak();
                        break;
                    case 5:
                        $this->takeLunch();
                        break;
                    case 6:
                        $this->endLunch();
                        break;
                    case 7:
                        $this->createUser();
                        break;
                    case 8:
                        $allNamesAdminStatus = $this->database->getAllUserNamesAdminStatus();
                        echo($allNamesAdminStatus);
                        break;
                    case 9:
                        $this->runReport();
                        break;
                    case 10:
                        $this->runFilteredReport();
                        break;
                    case 0:
                        fclose($stdin);
                        $runApplication = false;
                        break;
                    default:
                        echo($this->invalidSelectionMessage);
                }
            }
        }
    }

    private function checkEscapeText() {
        fscanf(STDIN, "%s\n", $input);
        if(is_string($input)) {
            return (trim($input) === "QUIT") ? false : $input;
        }
        return $input;
    }

    private function getTextPrompt($message) {
        $textInput = null;
        while(!is_string($textInput)) {
            echo $message;
            $textInput = $this->checkEscapeText();
            if($textInput === true) {
                break;
            }
            if(!is_string($textInput)) {
                echo($this->invalidInputMessage);
            }
        }
        return $textInput;
    }

    private function getUserIdPrompt($message) {
        $id = null;
        while(!is_int($id)) {
            echo($message);
            $id = $this->checkEscapeText();
            if($id === false) {
                break;
            }
            if(is_numeric($id)) {
                $id = intval($id);
            }
            if(!is_int($id)) {
                echo($this->invalidInputMessage);
            }
        }
        return $id;
    }

    private function getUserIdPromptWithValidation($message) {
        $id = $this->getUserIdPrompt($message);
        if($id === false) {
            return false;
        }
        $userExists = $this->database->checkUserIdExists($id);
        if(!$userExists) {
            echo($this->userNotInSystemFailureMessage);
            $id = $this->getUserIdPromptWithValidation($message);
        }
        return $id;
    }

    private function getUserPrompt($message) {
        $id = $this->getUserIdPromptWithValidation($message);
        if($id === false) {
            return false;
        }
        $user = $this->database->searchUserById($id);
        return $user;
    }

    private function actionSuccess($user, $actionFunction, $message) {
        $user->$actionFunction();
        echo($message);
        return true;
    }

    private function executeClockedInEmployeeAction($user, $checkFunction, $actionFunction, $successMessage, $errorMessage) {
        if($user) {
            $userClockedIn = $this->executeEmployeeAction(
                $user,
                'hasActiveShift',
                false,
                $this->clockInSuccessMessage,
                $this->userNotClockedInFailureMessage
            );

            if ($userClockedIn) {
                $this->executeEmployeeAction(
                    $user,
                    $checkFunction,
                    $actionFunction,
                    $successMessage,
                    $errorMessage
                );
            }
        }
    }

    private function executeEmployeeAction($user, $checkFunction, $actionFunction, $successMessage, $errorMessage) {
        if($user) {
            if ($user->isAdmin() || $user->$checkFunction()) {
                return !$actionFunction || $this->actionSuccess($user, $actionFunction, $successMessage);
            } else {
                echo($errorMessage);
            }
        }
        return false;
    }

    private function clockIn() {
        $message = $this->enterEmployeeIdMessage . $this->clockInMessage;
        $user = $this->getUserPrompt($message);
        $this->executeEmployeeAction(
            $user,
            'noActiveShift',
            'startShift',
            $this->clockInSuccessMessage,
            $this->clockInFailureMessage
        );
    }

    private function clockOut() {
        $message = $this->enterEmployeeIdMessage . $this->clockOutMessage;
        $user = $this->getUserPrompt($message);
        $this->executeClockedInEmployeeAction(
            $user,
            'hasNoBreaks',
            'endShift',
            $this->clockOutSuccessMessage,
            $this->activeBreakFailureMessage
        );
    }

    private function takeBreak() {
        $message = $this->enterEmployeeIdMessage . $this->takeBreakMessage;
        $user = $this->getUserPrompt($message);
        $this->executeClockedInEmployeeAction(
            $user,
            'noActiveBreak',
            'startBreak',
            $this->breakStartSuccessMessage,
            $this->breakStartFailureMessage
        );
    }

    private function endBreak() {
        $message = $this->enterEmployeeIdMessage . $this->endBreakMessage;
        $user = $this->getUserPrompt($message);
        $this->executeClockedInEmployeeAction(
            $user,
            'hasActiveBreak',
            'endBreak',
            $this->breakEndSuccessMessage,
            $this->breakEndFailureMessage
        );
    }

    private function takeLunch() {
        $message = $this->enterEmployeeIdMessage . $this->takeLunchMessage;
        $user = $this->getUserPrompt($message);
        $this->executeClockedInEmployeeAction(
            $user,
            'noActiveLunch',
            'startLunch',
            $this->lunchStartSuccessMessage,
            $this->lunchStartFailureMessage
        );
    }

    private function endLunch() {
        $message = $this->enterEmployeeIdMessage . $this->endLunchMessage;
        $user = $this->getUserPrompt($message);
        $this->executeClockedInEmployeeAction(
            $user,
            'hasActiveLunch',
            'endLunch',
            $this->lunchEndSuccessMessage,
            $this->lunchEndFailureMessage
        );
    }

    private function parseAdminResponse($adminResponse) {
        $admin = false;
        if(strtolower($adminResponse)[0] === 'y') {
            $admin = true;
        }
        return $admin;
    }

    private function createUser() {
        $userAlreadyExists = true;
        while($userAlreadyExists) {
            $id = $this->getUserIdPrompt($this->enterNewEmployeeIdMessage);
            if(!$id) {
                return;
            }
            $userAlreadyExists = $this->database->checkUserIdExists($id);
            if (!$userAlreadyExists) {
                $employeeName = $this->getTextPrompt($this->enterEmployeeNameMessage);
                if(!$employeeName) {
                    return;
                }
                $adminResponse = $this->getTextPrompt($this->enterEmployeeAdminMessage);
                if(!$adminResponse) {
                    return;
                }
                $admin = $this->parseAdminResponse($adminResponse);
                $this->database->registerUser($id, $employeeName, $admin);
            } else {
                echo($this->userAlreadyExistsFailureMessage);
            }
        }
    }

    private function generateReport($user) {
        if($user) {
            $data = $user->getShiftData();
            $this->reportForEmployeeAdminMessage;
            $employeeName = $this->reportForEmployeeNameMessage . $user->getName();
            $employeeId = $this->reportForEmployeeIdMessage . $user->getId();
            $employeeAdmin = $this->reportForEmployeeAdminMessage . $user->getAdminStatus();
            $employeeInformation = $employeeName . $employeeId . $employeeAdmin . "\n";
            $employeeData = implode("\n", $data);
            echo($employeeInformation . $employeeData);
        }
    }

    private function runReport() {
        $users = $this->database->getAllUsers();
        foreach($users as $user) {
            $this->generateReport($user);
        }
    }

    private function runFilteredReport() {
        $message = $this->reportOnEmployeeMessage;
        $user = $this->getUserPrompt($message);
        $this->generateReport($user);
    }
}