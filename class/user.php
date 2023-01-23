<?php
/**
 *  login/registration user class.
 *  Created by PhpStorm.
 *  User: amir.zamani
 *  Date: 12.09.18
 *  Time: 10:56
 */

class User
{
    /** @var object $pdo Copy of PDO connection */
    private $pdo;
    /** @var object of the logged in user */
    private $user;
    /** @var string error msg */
    public $msg;
    /** @var int number of permitted wrong login attemps */
    private $permitedAttemps = 5;

    /**
     * Connection init function
     * @param string $conString DB connection string.
     * @param string $user DB user.
     * @param string $pass DB password.
     *
     * @return bool Returns connection success.
     */
    public function dbConnect($conString, $user, $pass)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            try {
                $pdo = new PDO($conString, $user, $pass);
                $this->pdo = $pdo;
                return true;
            } catch (PDOException $e) {
                $this->msg = 'Connection did not work out!';
                return false;
            }
        } else {
            $this->msg = 'Session did not start.';
            return false;
        }
    }

    /**
     * Return the logged in user.
     * @return user array data
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Login function
     * @param string $email User email.
     * @param string $password User password.
     *
     * @return bool Returns login success.
     */
    public function login($email, $password)
    {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $this->error = json_encode(array("message" => "$email is not a valid email address", "status" => false));                   
          return false;
        }

        if (is_null($this->pdo)) {
            $this->msg = 'Connection did not work out!';
            $this->error = json_encode(array("message" => $this->msg, "status" => false));
            return false;
        } else {
            $pdo = $this->pdo;
            $stmt = $pdo->prepare('SELECT id, fname, lname, email, wrong_logins, password, user_role FROM users WHERE email = ? and confirmed = 1 limit 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (password_verify($password, $user['password'])) {
                if ($user['wrong_logins'] <= $this->permitedAttemps) {
                    $this->user = $user;
                    session_regenerate_id();
                    $_SESSION['user']['id'] = $user['id'];
                    $_SESSION['user']['fname'] = $user['fname'];
                    $_SESSION['user']['lname'] = $user['lname'];
                    $_SESSION['user']['email'] = $user['email'];
                    $_SESSION['user']['user_role'] = $user['user_role'];
                    $this->success = json_encode(array("message" => 'Logged in successfully.', "status" => true));
                    return true;
                } else {
                    $this->msg = 'This user account is blocked, please contact our support department.';
                    $this->error = json_encode(array("message" => $this->msg, "status" => false));                   
                    return false;
                }
            } else {
                $this->registerWrongLoginAttemp($email);
                $this->msg = 'Invalid login information or the account is not activated.';
                $this->error = json_encode(array("message" => $this->msg, "status" => false));
                return false;
            }
        }
    }

    /**
     * Register a wrong login attemp function
     * @param string $email User email.
     * @return void.
     */
    private function registerWrongLoginAttemp($email)
    {
        $pdo = $this->pdo;
        $stmt = $pdo->prepare('UPDATE users SET wrong_logins = wrong_logins + 1 WHERE email = ?');
        $stmt->execute([$email]);
    }

    public function imageUpload($imageFile){
                
      $fileName = $imageFile['name'];
      $tempPath = $imageFile['tmp_name'];
      $fileSize = $imageFile['size'];

      $upload_path = 'upload/'; // set upload folder path 

      $fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
          
      // valid image extensions
      $valid_extensions = array('jpeg', 'jpg', 'png', 'gif'); 
                      
      // allow valid image file formats
      if(in_array($fileExt, $valid_extensions))
      {               
        //check file not exist our upload folder path
        if(!file_exists($upload_path . $fileName))
        {
            // check file size '5MB'
            if($fileSize < 5000000)
            {
                return move_uploaded_file($tempPath, $upload_path . $fileName); // move file from system temporary path to our upload folder path 
            }
            else
            {       
              $this->error = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => false));   
            }
        }
        else
        {       
          $this->error = json_encode(array("message" => "Sorry, file already exists check upload folder", "status" => false));   
        }
      }
      else
      {       
        $this->error = json_encode(array("message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed", "status" => false));   
      }
            
      return false;

    }
    /**
     * Register a new user account function
     * @param string $email User email.
     * @param string $fname User first name.
     * @param string $lname User last name.
     * @param string $pass User password.
     * @return boolean of success.
     */
    public function registration($email, $fname, $lname, $pass, $image=array())
    {

        $pdo = $this->pdo;
        if ($this->checkEmail($email)) {
            $this->msg = 'This email is already taken.';
            $this->error = json_encode(array("message" => $this->msg, "status" => false));
            return false;
        }
        if (!(isset($email) && isset($fname) && isset($lname) && isset($pass) && filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $this->msg = 'Inesrt all valid requered fields.';
            $this->error = json_encode(array("message" => $this->msg, "status" => false));
            return false;
        }

        if(isset($image['name']) && !empty($image['name'])){
          if(!$this->imageUpload($image))
            return false;
        }else{
          $image['name'] = '';
        }

        $confirmed = 1;
        $pass = $this->hashPass($pass);
        $confCode = $this->hashPass(date('Y-m-d H:i:s') . $email);
        $stmt = $pdo->prepare('INSERT INTO users (fname, lname, email, password, confirm_code, confirmed, image) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$fname, $lname, $email, $pass, $confCode, $confirmed, $image['name']])) {
            $this->msg = 'Congratulation <b>'.$fname.'</b> you are registered successfully!';
            return true;
        } else {
            $this->error = json_encode(array("message" => "Inesrting a new user failed.", "status" => false));
            return false;
        }
    }

    /**
     * Check if email is already used function
     * @param string $email User email.
     * @return boolean of success.
     */
    private function checkEmail($email)
    {
        $pdo = $this->pdo;
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? limit 1');
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Password hash function
     * @param string $password User password.
     * @return string $password Hashed password.
     */
    private function hashPass($pass)
    {
        return password_hash($pass, PASSWORD_DEFAULT);
    }

    /**
     * Email the confirmation code function
     * @param string $email User email.
     * @return boolean of success.
     */
    private function sendConfirmationEmail($email)
    {
        $pdo = $this->pdo;
        $stmt = $pdo->prepare('SELECT confirm_code FROM users WHERE email = ? limit 1');
        $stmt->execute([$email]);
        $code = $stmt->fetch();

        $subject = 'Confirm your registration';
        $message = 'Please confirm you registration by pasting this code in the confirmation box: ' . $code['confirm_code'];
        $headers = 'X-Mailer: PHP/' . phpversion();

        if (mail($email, $subject, $message, $headers)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Password change function
     * @param int $id User id.
     * @param string $pass New password.
     * @return boolean of success.
     */
    public function passwordChange($id, $pass)
    {
        $pdo = $this->pdo;
        if (isset($id) && isset($pass)) {
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            if ($stmt->execute([$id, $this->hashPass($pass)])) {
                return true;
            } else {
                $this->msg = 'Password change failed.';
                return false;
            }
        } else {
            $this->msg = 'Provide an ID and a password.';
            return false;
        }
    }

    /**
     * Assign a role function
     * @param int $id User id.
     * @param int $role User role.
     * @return boolean of success.
     */
    public function assignRole($id, $role)
    {
        $pdo = $this->pdo;
        if (isset($id) && isset($role)) {
            $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            if ($stmt->execute([$id, $role])) {
                return true;
            } else {
                $this->msg = 'Role assign failed.';
                return false;
            }
        } else {
            $this->msg = 'Provide a role for this user.';
            return false;
        }
    }

    /**
     * User information change function
     * @param int $id User id.
     * @param string $fname User first name.
     * @param string $lname User last name.
     * @return boolean of success.
     */
    public function userUpdate($id, $fname, $lname)
    {
        $pdo = $this->pdo;
        if (isset($id) && isset($fname) && isset($lname)) {
            $stmt = $pdo->prepare('UPDATE users SET fname = ?, lname = ? WHERE id = ?');
            if ($stmt->execute([$id, $fname, $lname])) {
                return true;
            } else {
                $this->msg = 'User information change failed.';
                return false;
            }
        } else {
            $this->msg = 'Provide a valid data.';
            return false;
        }
    }

    /**
     * Print error msg function
     * @return void.
     */
    public function printMsg()
    {
        print $this->msg;
    }

    /**
     * Logout the user and remove it from the session.
     *
     * @return true
     */
    public function logout()
    {
        $_SESSION['user'] = null;
        session_regenerate_id();
        return true;
    }

    /**
     * Template for index head function
     * @return void.
     */
    public function indexHead()
    {
        print $this->render(indexHead);
    }

    /**
     * Simple template rendering function
     * @param string $path path of the template file.
     * @return void.
     */
    public function render($path, $vars = '')
    {
        ob_start();
        include($path);
        return ob_get_clean();
    }

    /**
     * Template for index top function
     * @return void.
     */
    public function indexTop()
    {
        print $this->render(indexTop);
    }

    /**
     * Template for login form function
     * @return void.
     */
    public function loginForm()
    {
        print $this->render(loginForm);
    }

    /**
     * Template for activation form function
     * @return void.
     */
    public function activationForm()
    {
        print $this->render(activationForm);
    }

    /**
     * Template for index middle function
     * @return void.
     */
    public function indexMiddle()
    {
        print $this->render(indexMiddle);
    }

    /**
     * Template for register form function
     * @return void.
     */
    public function registerForm()
    {
        print $this->render(registerForm);
    }

    /**
     * Template for index footer function
     * @return void.
     */
    public function indexFooter()
    {
        print $this->render(indexFooter);
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id']);
    }

    public function getPandingSplitPtm()
    {
      $pdo = $this->pdo;      
      $sql = "SELECT `id` FROM `SplitPtmMain` spm 
                  WHERE `Userid` = '".$_SESSION['user']['id']."' 
                    AND `totSplitAmt` < `amounttosplit`
              LIMIT 1
              ";
      $pdo = $this->pdo;
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $id = $stmt->fetch();
      if(empty($id[0]))
        return false;

      return $id[0];
    }

    public function hasSplitPtmPending()
    {
      $result = $this->getPandingSplitPtm();
      return !empty($result);
    }

    public function validateSplitPtmPost()
    {
      if(isset($_POST['amounttosplit']) && is_numeric($_POST['amounttosplit']) && $_POST['amounttosplit']>0){
        $this->amounttosplit = $_POST['amounttosplit'];
      }else{
        $this->error['amounttosplit'] = 'Invalid Amount!';
        return false;
      }

      if(isset($_POST['splitAmt']) && is_numeric($_POST['splitAmt']) && $_POST['splitAmt']>0 && $_POST['splitAmt'] <= $this->amounttosplit){
        $this->splitAmt = $_POST['splitAmt'];
      }else{
        $this->error['splitAmt'] = 'Invalid Share Amount!';
        return false;
      }

      $email = filter_input(INPUT_POST, 'emailtonotifiy', FILTER_SANITIZE_EMAIL);
      if(!empty($email)){
        $this->emailtonotifiy = $email;
      }else{
        $this->error['emailtonotifiy'] = 'Invalid Email!';
        return false;
      }

      //check for split to same person by same user for same created account
      $sql = "SELECT sp.id 
                  FROM SplitPtmSub sp 
                      JOIN SplitPtmMain spm ON (spm.id = sp.SpMid)
                  WHERE sp.status = 0 AND 
                        spm.Userid = '".$_SESSION['user']['id']."' AND 
                        sp.emailtonotifiy = '".$this->emailtonotifiy."'
              ";
      $pdo = $this->pdo;
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $found = $stmt->fetch();

      if(!empty($found)){

        $this->error['emailtonotifiy'] = 'Invalid Sharing!';
        return false;

      }

      return true;

    }

    public function store_splitAccount($value='')
    {

      $pdo = $this->pdo;
      //find panding split
      $activeSplitID = $this->getPandingSplitPtm();
      if(!$activeSplitID){
        $stmt = $pdo->prepare("INSERT INTO `SplitPtmMain` 
                                  SET `Userid` = '".$_SESSION['user']['id']."',
                                      `amounttosplit` = '".$this->amounttosplit."',
                                      `totSplitAmt` = `totSplitAmt` + '".$this->splitAmt."'
                              ");  
        $stmt->execute();
        $activeSplitID = $pdo->lastInsertId();
      }else{
        $stmt = $pdo->prepare("UPDATE `SplitPtmMain` 
                                  SET `totSplitAmt` = `totSplitAmt` + '".$this->splitAmt."'
                              ");  
        $stmt->execute();        
      }
      //create new split and send notification

      unset($pdo);
      $pdo = $this->pdo;
      $stmt1 = $pdo->prepare("INSERT INTO `SplitPtmSub` 
                                SET `SpMid` = '".$activeSplitID."',
                                    `splitAmt` = '".$this->splitAmt."',
                                    `emailtonotifiy` = '".$this->emailtonotifiy."'
                            ");  
      $stmt1->execute();
      $newSplitID = $pdo->lastInsertId();

      if($this->sendSplitPtmNotificationEmail($this->emailtonotifiy,$this->splitAmt)){
        $this->success['notification'] = 'Notified to payer successfully!';
        return true;
      }else{
        $this->error['notification'] = 'Unable to notify payer!';
        return false;
      }

    }

    /**
     * Email to payer
     * @param string $email User email.
     * @return boolean of success.
     */
    private function sendSplitPtmNotificationEmail($email,$amountToPay)
    {

        $pdo = $this->pdo;
        $stmt = $pdo->prepare("SELECT CONCAT(fname,' ',lname) as `name` FROM users WHERE id = ? limit 1");
        $stmt->execute([$_SESSION['user']['id']]);
        $from = $stmt->fetch();

        $payReqLink = baseUrl().'/notify.php?email='.$email;
        $subject = 'Payment Request';
        $message = $from[0].' has requested to pay '.money_format('%i', $amountToPay).'</br> Please follow link to pay <a href="'.$payReqLink.'">Click Here</a>';
        $headers = 'X-Mailer: PHP/' . phpversion();

        /* debug
        $html = '<table>';
        $html .= '<tr>';
        $html .= '<td>Subject: '.$subject.'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td>Message: '.$message.'</td>';
        $html .= '</tr>';
        $html .= '</table>';
        file_put_contents('email_'.strtotime('now').'.html', $html);
        */
        if (mail($email, $subject, $message, $headers)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Template for user page function
     * @return void.
     */
    public function userPage()
    {

        if($this->isLoggedIn()){

          if(isset($_POST['createNewSplit']) && $_POST['createNewSplit'] == 1){

            if($this->validateSplitPtmPost()){
              $this->store_splitAccount();
              die($this->msg);
            }else{
              $jsonMsgArr['error'] = $this->error;
              die(json_encode($jsonMsgArr));

            }
          }else if($this->hasSplitPtmPending() || isset($_GET['createNewSplit'])==1){

            $sql = "SELECT amounttosplit - totSplitAmt as `balance` FROM SplitPtmMain spm 
                        WHERE Userid = '".$_SESSION['user']['id']."' 
                          AND totSplitAmt < amounttosplit
                    ";
            $pdo = $this->pdo;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
           
            $sql = "SELECT sp.* 
                        FROM SplitPtmSub sp 
                            JOIN SplitPtmMain spm ON (spm.id = sp.SpMid)
                        WHERE spm.Userid = '".$_SESSION['user']['id']."' 
                            AND spm.amounttosplit > totSplitAmt 
                    ";
            $pdo = $this->pdo;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();    
            $result['notifications'] = $stmt->fetchAll();

            $_GET['createNewSplit'] = 1;

            print $this->render(userPage, $result);

        }else{

          $email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);
          $email = empty($email)?$_SESSION['user']['email']:$email;
          $data['notifications']['from'] = $this->getNotification($email,'from');
          $data['notifications']['to'] = $this->getNotification($email,'to');
          print $this->render(userPage, $data);
          echo '</br></br>';
          
        }

    }
  }

    public function getNotification($email='',$paymentRequested='')
    {

        if (is_null($this->pdo)) {
            $this->msg = 'Connection did not work out!';
            return [];
        } else {

            $Userid = !empty($_SESSION['user']['id'])?$_SESSION['user']['id']:0;
            $emailtonotifiy = $email;

            if(!empty($Userid) || !empty($emailtonotifiy)){

                if($paymentRequested==''){

                  $sql = "SELECT sp.*, CONCAT(u.fname,',',u.lname) `fromName`
                              FROM SplitPtmSub sp 
                                  JOIN SplitPtmMain spm ON (spm.id = sp.SpMid)
                                  JOIN users u ON (u.id = spm.Userid)
                              WHERE (spm.Userid = ".$Userid." 
                                OR sp.emailtonotifiy = '".$emailtonotifiy."') AND sp.status = 0 
                          ";
                  $pdo = $this->pdo;
                  $stmt = $pdo->prepare($sql);
                  $stmt->execute();
                  $result = $stmt->fetchAll();
                  return $result;

                }else if($paymentRequested=='from'){

                  $sql = "SELECT sp.*, CONCAT(u.fname,',',u.lname) `fromName`
                              FROM SplitPtmSub sp 
                                  JOIN SplitPtmMain spm ON (spm.id = sp.SpMid)
                                  JOIN users u ON (u.id = spm.Userid)
                              WHERE ((spm.Userid != ".$Userid." AND sp.emailtonotifiy = '".$_SESSION['user']['email']."')
                                      OR 
                                    (spm.Userid = ".$Userid." AND sp.emailtonotifiy = '".$_SESSION['user']['email']."')
                                    )
                                AND sp.status = 0
                          ";
                  $pdo = $this->pdo;
                  $stmt = $pdo->prepare($sql);
                  $stmt->execute();
                  $result = $stmt->fetchAll();
                  return $result;    

                }else if($paymentRequested=='to'){

                  $sql = "SELECT sp.*, CONCAT(u.fname,',',u.lname) `toName`
                              FROM SplitPtmSub sp 
                                  JOIN SplitPtmMain spm ON (spm.id = sp.SpMid)
                                  JOIN users u ON (u.id = spm.Userid)
                              WHERE spm.Userid = ".$Userid."
                          ";
                  $pdo = $this->pdo;
                  $stmt = $pdo->prepare($sql);
                  $stmt->execute();
                  $result = $stmt->fetchAll();
                  return $result;  

                }

            }else{
                return false;
            }
        }
    }
    /**
     * List users function
     *
     * @return array Returns list of users.
     */
    public function listUsers()
    {
        if (is_null($this->pdo)) {
            $this->msg = 'Connection did not work out!';
            return [];
        } else {
            $pdo = $this->pdo;
            $stmt = $pdo->prepare('SELECT id, fname, lname, email FROM users WHERE confirmed = 1');
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $result;
        }
    }

    public function notify($email)
    {

      $data['name'] = $email;
      //find registered user.

      $sql = "SELECT * FROM users WHERE email = '".$email."' LIMIT 1";
      $pdo = $this->pdo;
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $found = $stmt->fetch();

      //registered email
      if(!empty($found['id'])){
        $data['name'] = $found['fname'].','.$found['lname'];
      }
      $data['notifications'] = $this->getNotification($email);
      print $this->render(notify,$data);

    }

    public function paynow($id)
    {

      $sql = "SELECT id 
                FROM SplitPtmSub 
                WHERE id = '".$id."' 
                  AND emailtonotifiy = '".$_SESSION['user']['email']."'
                  AND status = 0 
                LIMIT 1";
      $pdo = $this->pdo;
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $found = $stmt->fetch();

      if(!empty($found['id'])){
        $pdo = $this->pdo;
        $sql = "UPDATE SplitPtmSub SET status = 2 WHERE id = '".$found['id']."'";
        $stmt = $pdo->prepare($sql);
        if($stmt->execute()){
          return true;
        }
        return false;
      }

    }

    public function rejectPayment($email='',$id=0)
    {

      $sql = "SELECT id 
                FROM SplitPtmSub 
                WHERE (id = '".$id."' OR emailtonotifiy = '".$email."')
                  AND status = 0 
                LIMIT 1";
      $pdo = $this->pdo;
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $found = $stmt->fetch();

      if(!empty($found['id'])){
        $pdo = $this->pdo;
        $sql = "UPDATE SplitPtmSub sps JOIN SplitPtmMain spm ON (spm.id = sps.SpMid) 
                    SET sps.status = 3,
                        spm.totSplitAmt = spm.totSplitAmt - sps.splitAmt
                  WHERE sps.id = '".$found['id']."'
               ";
        $stmt = $pdo->prepare($sql);
        if($stmt->execute()){
          return true;
        }
        return false;
      }

    }
}
