<?php

class Expenses extends Database {
    public $errors = [];

    public function insert_validation_expenses($data) {
        $amount_exp = $data['amount_exp'];
        $date_exp = $data['date_exp'];
        $item_id = $data['item_id'];

        if (empty($amount_exp) || empty($date_exp) || empty($item_id)) {
            $this->errors['message'] = "All fields are required.";
            return false;
        }

        if (!is_numeric($amount_exp) || $amount_exp <= 0) {
            $this->errors['message'] = "Amount spent must be a positive number.";
            return false;
        }

        if ($this->is_expense_duplicate($item_id, $date_exp)) {
            $this->errors['message'] = "An expense for this item on the same date already exists.";
            return false;
        }

        return true;
    }

    public function is_expense_duplicate($item_id, $date_exp) {
        $conn = $this->openConnection();

        $query = "SELECT COUNT(*) FROM EXPENSES WHERE ITEM_ID = :item_id AND EXP_DATE = :date_exp";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->bindParam(':date_exp', $date_exp, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function insert_expenses($data) {
        $conn = $this->openConnection();

        $amount_exp = $data['amount_exp'];
        $date_exp = $data['date_exp'];
        $item_id = $data['item_id'];

        if (!$this->insert_validation_expenses($data)) {
            return false;
        }

        try {
            $query = "INSERT INTO EXPENSES (EXP_AMOUNT, EXP_DATE, ITEM_ID) 
                        VALUES (:amount_exp, :date_exp, :item_id)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':amount_exp', $amount_exp);
            $stmt->bindParam(':date_exp', $date_exp);
            $stmt->bindParam(':item_id', $item_id);

            if ($stmt->execute()) {
                $this->errors['message'] = "Expense added successfully!";
                return true;
            } else {
                $this->errors['message'] = "Failed to add expense.";
                return false;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not add expense. " . $e->getMessage();
            return false;
        }
    }

    public function delete_expenses($data) {
        $conn = $this->openConnection();

        try {

            $query = "DELETE FROM EXPENSES WHERE EXP_ID = :exp_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':exp_id', $data['exp_id']);

            if ($stmt->execute()) {
                $this->errors['message'] = "Expense deleted successfully!";
                return true;
            } else {
                $this->errors['message'] = "Failed to delete expense.";
                return false;
            }
        } catch (PDOException $e) {
            $this->errors['message'] = "Could not delete expense. " . $e->getMessage();
            return false;
        }
    }

    public function getAllExpenses() {
        $conn = $this->openConnection();

        try {
            $query = "SELECT e.EXP_ID, e.EXP_AMOUNT, e.EXP_DATE, e.EXP_ADDED, i.ITEM_NAME
                      FROM EXPENSES e
                      JOIN ITEM i ON e.ITEM_ID = i.ITEM_ID
                      ORDER BY e.EXP_DATE DESC";
            $stmt = $conn->query($query);
            $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $expenses;
        } catch (PDOException $e) {
            $this->errors['message'] = "Error fetching expenses. " . $e->getMessage();
            return [];
        }
    }

}