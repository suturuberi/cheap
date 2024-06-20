<?php

class User extends Database{

    public $errors = [];

	public function login ($data) {
        $conn = $this->openConnection();

        if (isset($_POST['login'])) {
            try {
                $password = md5($data['password']);

                $select = $conn->prepare("SELECT 
                                            SA.SUPERAD_ID as user_id,
                                            SA.USERT_ID as user_type_id,
                                            UT.USERT_DESCRIPT as user_type_description
                                        FROM 
                                            SUPERADMIN SA
                                        JOIN 
                                            USER_TYPE UT ON SA.USERT_ID = UT.USERT_ID
                                        WHERE 
                                            SA.SUPERAD_EMAIL = ? AND SA.SUPERAD_PASS = ?
                                        UNION
                                        SELECT
                                            A.ADMIN_ID as user_id,
                                            A.USERT_ID as user_type_id,
                                            UT.USERT_DESCRIPT as user_type_description
                                        FROM 
                                            ADMIN A
                                        JOIN 
                                            USER_TYPE UT ON A.USERT_ID = UT.USERT_ID
                                        WHERE 
                                            A.ADMIN_EMAIL = ? AND A.ADMIN_PASS = ?;");

                $select->execute([$data['email'], $password, $data['email'], $password]);
                $row = $select->fetch(PDO::FETCH_ASSOC);

                if ($select->rowCount() > 0) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['user_type_id'] = $row['user_type_id'];

                    // Redirect based on user type
                    if ($row['user_type_description'] == 'SUPER ADMIN') {
                        redirect('orderssuperadmin');
                    } else if ($row['user_type_description'] == 'ADMIN') {
                        redirect('ordersadmin');
                    } else {
                        $this->errors['message'] = "Account is not recognized.";
                    }
                } else {
                    $this->errors['message'] = "Incorrect email or password.";
                }
            } catch (PDOException $e) {
                $this->errors['message'] = "Could not login." . $e->getMessage();
            }
        }
    }

    public function getSuperadminDetails($user_id) {
        $conn = $this->openConnection();
    
        $select = $conn->prepare("SELECT SUPERAD_FNAME as user_fname, SUPERAD_MNAME as user_mname, SUPERAD_LNAME as user_lname, SUPERAD_EMAIL as user_email, SUPERAD_PASS as user_password
                    FROM SUPERADMIN WHERE SUPERAD_ID = ?");
        $select->execute([$user_id]);
        $result = $select->fetch(PDO::FETCH_ASSOC);
    
        return $result;
    }
    
    public function getAdminDetails($user_id) {
        $conn = $this->openConnection();
    
        $select = $conn->prepare("SELECT ADMIN_FNAME as user_fname, ADMIN_MNAME as user_mname, ADMIN_LNAME as user_lname, ADMIN_EMAIL as user_email, ADMIN_PASS as user_password 
                    FROM ADMIN WHERE ADMIN_ID = ?");
        $select->execute([$user_id]);
        $result = $select->fetch(PDO::FETCH_ASSOC);
    
        return $result;
    }

    public function getUserDetails() {
        if ($_SESSION['user_type_id'] == 1) { 
            return $this->getSuperadminDetails($_SESSION['user_id']);
        } else if ($_SESSION['user_type_id'] == 2) { 
            return $this->getAdminDetails($_SESSION['user_id']);
        } else {
            return null;
        }
    }

    public function change_pass($data, $user_id)
    {
        $conn = $this->openConnection();

        if ($this->validate_change_pass($data, $user_id)) {
            try {
                $password = md5($data['n_pass']);
                if ($_SESSION['user_type_id'] == 1) { // Super Admin
                    $update = "UPDATE SUPERADMIN SET SUPERAD_PASS = :n_pass WHERE SUPERAD_ID = :user_id";
                } else if ($_SESSION['user_type_id'] == 2) { // Admin
                    $update = "UPDATE ADMIN SET ADMIN_PASS = :n_pass WHERE ADMIN_ID = :user_id";
                } else {
                    $this->errors['message'] = "User type is not recognized.";
                    return false;
                }

                $stmt = $conn->prepare($update);
                $stmt->bindParam(':n_pass', $password);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        $this->errors['message'] = "Password changed successfully.";
                        return true;
                    } else {
                        $this->errors['message'] = "Unable to change password.";
                    }
                } else {
                    $this->errors['message'] = "Password not changed.";
                }
            } catch (PDOException $e) {
                $this->errors['message'] = "Could not change password. " . $e->getMessage();
            }
        }

        return false;
    }

    public function validate_change_pass($data, $user_id)
    {
        $this->errors = [];

        $data = array_merge(['c_pass' => '', 'n_pass' => '', 'cpass' => ''], $data);

        $user_details = $this->getUserDetails();

        if ($user_details) {
            $current_hashed_password = $user_details['user_password'];
            $c_password = md5($_POST['c_pass']);
            
            if (empty($data['c_pass']) || empty($data['n_pass']) || empty($data['cpass'])) {
                $this->errors['validate_all'] = "All fields are required.";
                return false;
            }

            if ($c_password !== $current_hashed_password) {
                $this->errors['validate_cpass'] = "Current password is incorrect.";
                return false;
            }

            if (strlen($data['n_pass']) < 8) {
                $this->errors['validate_npass'] = "New password must be at least 8 characters long.";
                return false;
            }

            if ($data['n_pass'] !== $data['cpass']) {
                $this->errors['validate_c_pass'] = "New password and confirmation password do not match.";
                return false;
            }

            return true;
        } else {
            $this->errors['message'] = "User details not found.";
        }

        return false;
    }

}