package com.example.visual1.controller;

import com.example.visual1.model.Transaction;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.stage.Stage;

public class TransactionDialogController {

    @FXML private ComboBox<String> typeComboBox;
    @FXML private TextField categoryField;
    @FXML private TextField amountField;
    @FXML private DatePicker datePicker;
    @FXML private TextField descriptionField;

    private Stage dialogStage;
    private Transaction transaction;
    private boolean okClicked = false;

    @FXML
    public void initialize() {
        typeComboBox.setItems(FXCollections.observableArrayList("Доход", "Расход"));
    }

    public void setDialogStage(Stage dialogStage) {
        this.dialogStage = dialogStage;
    }

    public void setTransaction(Transaction transaction) {
        this.transaction = transaction;
        typeComboBox.setValue(transaction.getType());
        categoryField.setText(transaction.getCategory());
        amountField.setText(transaction.getAmount() > 0 ? String.valueOf(transaction.getAmount()) : "");
        datePicker.setValue(transaction.getDate());
        descriptionField.setText(transaction.getDescription());
    }

    public boolean isOkClicked() {
        return okClicked;
    }

    @FXML
    private void handleSave() {
        if (isInputValid()) {
            transaction.setType(typeComboBox.getValue());
            transaction.setCategory(categoryField.getText());
            transaction.setAmount(Double.parseDouble(amountField.getText()));
            transaction.setDate(datePicker.getValue());
            transaction.setDescription(descriptionField.getText());

            okClicked = true;
            dialogStage.close();
        }
    }

    @FXML
    private void handleCancel() {
        dialogStage.close();
    }

    private boolean isInputValid() {
        StringBuilder errorMessage = new StringBuilder();

        if (typeComboBox.getValue() == null) {
            errorMessage.append("Выберите тип операции!\n");
        }
        if (categoryField.getText() == null || categoryField.getText().trim().isEmpty()) {
            errorMessage.append("Введите категорию!\n");
        }
        if (amountField.getText() == null || amountField.getText().trim().isEmpty()) {
            errorMessage.append("Введите сумму!\n");
        } else {
            try {
                double amount = Double.parseDouble(amountField.getText());
                if (amount <= 0) {
                    errorMessage.append("Сумма должна быть больше 0!\n");
                }
            } catch (NumberFormatException e) {
                errorMessage.append("Некорректный формат суммы (должно быть число)!\n");
            }
        }
        if (datePicker.getValue() == null) {
            errorMessage.append("Выберите дату!\n");
        }

        if (errorMessage.length() == 0) {
            return true;
        } else {
            Alert alert = new Alert(Alert.AlertType.ERROR);
            alert.initOwner(dialogStage);
            alert.setTitle("Ошибка валидации");
            alert.setHeaderText("Исправьте некорректные поля");
            alert.setContentText(errorMessage.toString());
            alert.showAndWait();
            return false;
        }
    }
}