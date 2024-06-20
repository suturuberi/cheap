<?php

class Customer extends Database {
    public $errors = [];

    public function insert_cus_name_validation($data) {
        $conn = $this->openConnection();
        try {
            $cus_fname = strtoupper($data['cus_fname']);
            $cus_mname = strtoupper($data['cus_mname']);
            $cus_lname = strtoupper($data['cus_lname']);

            if (empty($cus_fname) || empty($cus_lname)) {
                $this->errors['message'] = "First name and last name cannot be empty.";
                return false;
            }

            if (!is_null($cus_mname) && strlen($cus_mname) > 1) {
                $this->errors['message'] = "Middle name can be null or only 1 character long.";
                return false;
            }

            if (strlen($cus_fname) > 50 || strlen($cus_lname) > 50) {
                $this->errors['message'] = "Length of first name and last name cannot exceed 50 characters each.";
                return false;
            }

            $check_query = "SELECT COUNT(*) FROM CUSTOMER WHERE UPPER(CUS_FNAME) = :cus_fname AND UPPER(CUS_MNAME) = :cus_mname AND UPPER(CUS_LNAME) = :cus_lname";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':cus_fname', $cus_fname);
            $check_stmt->bindParam(':cus_mname', $cus_mname);
            $check_stmt->bindParam(':cus_lname', $cus_lname);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $this->errors['message'] = "Customer already exists in the database.";
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating customer. " . $e->getMessage();
            return false;
        }
    }

    public function insert_cus_contact_validation($data) {
        $conn = $this->openConnection();
        try {
            $cus_contact = strtoupper($data['cus_contact']);

            if (empty($cus_contact)) {
                $this->errors['message'] = "Contact cannot be empty.";
                return false;
            }

            if (!preg_match('/^\d{11}$/', $cus_contact)) {
                $this->errors['message'] = "Contact number must be numeric and exactly 10 characters long.";
                return false;
            }

            $check_query = "SELECT COUNT(*) FROM CUSTOMER WHERE (CUS_CONTACT) = :cus_contact";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':cus_contact', $cus_contact);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $this->errors['message'] = "Customer with this contact number already exists in the database.";
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating contact number. " . $e->getMessage();
            return false;
        }
    }

    public function insert_customer($data, $admin_id) {
        $conn = $this->openConnection();
        if (isset($_POST['add_cus'])) {
            if (!$this->insert_cus_name_validation($data) || !$this->insert_cus_contact_validation($data)) {
                return;
            }

            try {
                $cus_fname = strtoupper($data['cus_fname']);
                $cus_mname = strtoupper($data['cus_mname']);
                $cus_lname = strtoupper($data['cus_lname']);
                $cus_contact = strtoupper($data['cus_contact']);

                $insert = "INSERT INTO CUSTOMER (CUS_FNAME, CUS_MNAME, CUS_LNAME, CUS_CONTACT, ADMIN_ID)
                            VALUES (:cus_fname, :cus_mname, :cus_lname, :cus_contact, :admin_id)";
                $stmt = $conn->prepare($insert);
                $stmt->bindParam(':cus_fname', $cus_fname);
                $stmt->bindParam(':cus_mname', $cus_mname);
                $stmt->bindParam(':cus_lname', $cus_lname);
                $stmt->bindParam(':cus_contact', $cus_contact);
                $stmt->bindParam(':admin_id', $admin_id);

                if ($stmt->execute()) {
                    $this->errors['message'] = "Customer created successfully!";
                } else {
                    $this->errors['message'] = "Failed to create customer.";
                }
            } catch (PDOException $e) {
                $this->errors['message'] = "Could not add customer." . $e->getMessage();
            }
        }
    }

    public function update_cus_name_validation($data) {
        $conn = $this->openConnection();
        try {
            $cus_id = $data['cus_id'];
            $cus_fname = strtoupper($data['cus_fname']);
            $cus_mname = strtoupper($data['cus_mname']);
            $cus_lname = strtoupper($data['cus_lname']);

            if (empty($cus_fname) || empty($cus_lname)) {
                $this->errors['message'] = "First name and last name cannot be empty.";
                return false;
            }

            if (strlen($cus_fname) > 50 || strlen($cus_lname) > 50) {
                $this->errors['message'] = "Length of first name and last name cannot exceed 50 characters each.";
                return false;
            }

            $check_query = "SELECT COUNT(*) FROM CUSTOMER 
                            WHERE UPPER(CUS_FNAME) = :cus_fname 
                            AND UPPER(CUS_MNAME) = :cus_mname 
                            AND UPPER(CUS_LNAME) = :cus_lname 
                            AND CUS_ID != :cus_id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':cus_fname', $cus_fname);
            $check_stmt->bindParam(':cus_mname', $cus_mname);
            $check_stmt->bindParam(':cus_lname', $cus_lname);
            $check_stmt->bindParam(':cus_id', $cus_id);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $this->errors['message'] = "Customer with this name combination already exists in the database.";
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating customer. " . $e->getMessage();
            return false;
        }
    }

    public function update_cus_contact_validation($data) {
        $conn = $this->openConnection();
        try {
            $cus_id = $data['cus_id'];
            $cus_contact = strtoupper($data['cus_contact']);

            if (empty($cus_contact)) {
                $this->errors['message'] = "Contact cannot be empty.";
                return false;
            }

            if (!preg_match('/^\d{10}$/', $cus_contact)) {
                $this->errors['message'] = "Contact number must be numeric and exactly 10 characters long.";
                return false;
            }

            $check_query = "SELECT COUNT(*) FROM CUSTOMER WHERE CUS_CONTACT = :cus_contact AND CUS_ID != :cus_id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':cus_contact', $cus_contact);
            $check_stmt->bindParam(':cus_id', $cus_id);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $this->errors['message'] = "Customer with this contact number already exists in the database.";
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating contact number. " . $e->getMessage();
            return false;
        }
    }

    public function update_customer($data) {
        $conn = $this->openConnection();
        if (isset($_POST['update_cus'])) {
            if (!$this->update_cus_name_validation($data) || !$this->update_cus_contact_validation($data)) {
                return;
            }

            try {
                $cus_id = $data['cus_id'];
                $cus_fname = strtoupper($data['cus_fname']);
                $cus_mname = strtoupper($data['cus_mname']);
                $cus_lname = strtoupper($data['cus_lname']);
                $cus_contact = strtoupper($data['cus_contact']);

                $update = "UPDATE CUSTOMER
                            SET CUS_FNAME = :cus_fname,
                                CUS_MNAME = :cus_mname,
                                CUS_LNAME = :cus_lname,
                                CUS_CONTACT = :cus_contact
                            WHERE CUS_ID = :cus_id";
                $stmt = $conn->prepare($update);
                $stmt->bindParam(':cus_id', $cus_id);
                $stmt->bindParam(':cus_fname', $cus_fname);
                $stmt->bindParam(':cus_mname', $cus_mname);
                $stmt->bindParam(':cus_lname', $cus_lname);
                $stmt->bindParam(':cus_contact', $cus_contact);

                if ($stmt->execute()) {
                    $this->errors['message'] = "Customer updated successfully!";
                } else {
                    $this->errors['message'] = "Failed to update customer.";
                }
            } catch (PDOException $e) {
                $this->errors['message'] = "Could not update customer. " . $e->getMessage();
            }
        }
    }

    public function delete_customer_validation($data) {
        $conn = $this->openConnection();
        try {
            $cus_id = $data['cus_id'];
    
            // Check if there are associated orders for the customer being deleted
            $check_order_query = "SELECT COUNT(*) FROM ORDERS WHERE CUS_ID = :cus_id";
            $check_order_stmt = $conn->prepare($check_order_query);
            $check_order_stmt->bindParam(':cus_id', $cus_id);
            $check_order_stmt->execute();
            $order_count = $check_order_stmt->fetchColumn();
    
            if ($order_count > 0) {
                $this->errors['message'] = "Cannot delete customer because it has associated orders.";
                return false;
            }
    
            return true;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error validating customer deletion." . $e->getMessage();
            return false;
        }
    }
    
    public function delete_customer($data) {
        $conn = $this->openConnection();

        if (!$this->delete_customer_validation($data)) {
            return;
        }

        try {
            $delete_query = "DELETE FROM CUSTOMER WHERE CUS_ID= :cus_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':cus_id', $data['cus_id']);

            if ($stmt->execute()) {
                $this->errors['message'] = "Customer deleted successfully!";
            } else {
                $this->errors['message'] = "Failed to delete customer.";
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete customer." . $e->getMessage();
        }
    }

    public function getAllCustomers() {
        $conn = $this->openConnection();

        try {
            $query = "SELECT * FROM CUSTOMER ORDER BY CUS_LNAME, CUS_FNAME, CUS_MNAME";
            $stmt = $conn->query($query);
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $customers;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching customers. " . $e->getMessage();
            return [];
        }
    }

    public function getAllCustomersWithAddedAdmin()
    {
        $conn = $this->openConnection();

        try {
            $query = "SELECT 
                        C.CUS_ID,
                        C.CUS_FNAME,
                        C.CUS_MNAME,
                        C.CUS_LNAME,
                        C.CUS_CONTACT,
                        A.ADMIN_FNAME AS ADMIN_FNAME,
                        A.ADMIN_MNAME AS ADMIN_MNAME,
                        A.ADMIN_LNAME AS ADMIN_LNAME
                    FROM 
                        CUSTOMER C
                    LEFT JOIN 
                        ADMIN A ON C.ADMIN_ID = A.ADMIN_ID
                    ORDER BY CUS_LNAME, CUS_FNAME, CUS_MNAME";
            $stmt = $conn->query($query);
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // show($customers);

            return $customers;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching customers. " . $e->getMessage();
            return [];
        }
    }

    public function getAllCustomersOrderByName() {
        $conn = $this->openConnection();

        try {
            $query = "SELECT * FROM CUSTOMER ORDER BY CUS_LNAME, CUS_FNAME, CUS_MNAME";
            $stmt = $conn->query($query);
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $customers;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching customers. " . $e->getMessage();
            return [];
        }
    }

}
