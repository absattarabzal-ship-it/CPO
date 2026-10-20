package com.example.visual1.controller;

import com.example.visual1.model.Transaction;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.collections.transformation.FilteredList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.GridPane;
import javafx.stage.Modality;
import javafx.stage.Stage;

import java.io.IOException;
import java.time.LocalDate;

public class MainController {

    @FXML private TableView<Transaction> transactionTable;
    @FXML private TableColumn<Transaction, LocalDate> dateColumn;
    @FXML private TableColumn<Transaction, String> typeColumn;
    @FXML private TableColumn<Transaction, String> categoryColumn;
    @FXML private TableColumn<Transaction, Double> amountColumn;
    @FXML private TableColumn<Transaction, String> descriptionColumn;

    @FXML private TextField searchField;
    @FXML private ComboBox<String> filterTypeComboBox;
    @FXML private Label balanceLabel;
    @FXML private Label statusLabel;

    private final ObservableList<Transaction> masterData = FXCollections.observableArrayList();
    private FilteredList<Transaction> filteredData;

    @FXML
    public void initialize() {
        dateColumn.setCellValueFactory(new PropertyValueFactory<>("date"));
        typeColumn.setCellValueFactory(new PropertyValueFactory<>("type"));
        categoryColumn.setCellValueFactory(new PropertyValueFactory<>("category"));
        amountColumn.setCellValueFactory(new PropertyValueFactory<>("amount"));
        descriptionColumn.setCellValueFactory(new PropertyValueFactory<>("description"));

        filterTypeComboBox.setItems(FXCollections.observableArrayList("Все", "Доход", "Расход"));
        filterTypeComboBox.setValue("Все");

        loadDemoData();

        filteredData = new FilteredList<>(masterData, p -> true);

        searchField.textProperty().addListener((observable, oldValue, newValue) -> updateFilter());
        filterTypeComboBox.valueProperty().addListener((observable, oldValue, newValue) -> updateFilter());

        transactionTable.setItems(filteredData);
        updateBalance();
    }

    private void updateFilter() {
        String searchText = searchField.getText() == null ? "" : searchField.getText().toLowerCase();
        String selectedType = filterTypeComboBox.getValue();

        filteredData.setPredicate(transaction -> {
            boolean matchesType = "Все".equals(selectedType) || transaction.getType().equalsIgnoreCase(selectedType);
            boolean matchesSearch = transaction.getDescription().toLowerCase().contains(searchText) ||
                    transaction.getCategory().toLowerCase().contains(searchText);
            return matchesType && matchesSearch;
        });
        updateBalance();
    }

    private void updateBalance() {
        double income = masterData.stream().filter(t -> "Доход".equals(t.getType())).mapToDouble(Transaction::getAmount).sum();
        double expense = masterData.stream().filter(t -> "Расход".equals(t.getType())).mapToDouble(Transaction::getAmount).sum();
        double balance = income - expense;
        balanceLabel.setText(String.format("Баланс: %.2f ₸ (Доходы: %.2f | Расходы: %.2f)", balance, income, expense));
    }

    @FXML
    private void handleNewTransaction() {
        Transaction temp = new Transaction("", "Расход", "", 0.0, LocalDate.now(), "");
        boolean okPressed = showTransactionDialog(temp, "Новая операция");
        if (okPressed) {
            masterData.add(temp);
            updateBalance();
            statusLabel.setText("Запись успешно добавлена.");
        }
    }

    @FXML
    private void handleEditTransaction() {
        Transaction selected = transactionTable.getSelectionModel().getSelectedItem();
        if (selected != null) {
            boolean okPressed = showTransactionDialog(selected, "Редактирование операции");
            if (okPressed) {
                transactionTable.refresh();
                updateBalance();
                statusLabel.setText("Запись обновлена.");
            }
        } else {
            showAlert("Ошибка", "Операция не выбрана", "Пожалуйста, выберите запись в таблице.");
        }
    }

    @FXML
    private void handleDeleteTransaction() {
        int selectedIndex = transactionTable.getSelectionModel().getSelectedIndex();
        if (selectedIndex >= 0) {
            masterData.remove(transactionTable.getItems().get(selectedIndex));
            updateBalance();
            statusLabel.setText("Запись удалена.");
        } else {
            showAlert("Ошибка", "Операция не выбрана", "Пожалуйста, выберите запись для удаления.");
        }
    }

    private boolean showTransactionDialog(Transaction transaction, String title) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/example/visual1/transaction_dialog.fxml"));
            GridPane page = loader.load();

            Stage dialogStage = new Stage();
            dialogStage.setTitle(title);
            dialogStage.initModality(Modality.WINDOW_MODAL);
            dialogStage.initOwner(transactionTable.getScene().getWindow());
            dialogStage.setScene(new Scene(page));

            TransactionDialogController controller = loader.getController();
            controller.setDialogStage(dialogStage);
            controller.setTransaction(transaction);

            dialogStage.showAndWait();
            return controller.isOkClicked();
        } catch (IOException e) {
            e.printStackTrace();
            return false;
        }
    }

    @FXML private void handleAbout() {
        showAlert("О программе", "Personal Finance Tracker v1.0", "Мини-проект по дисциплине «Визуальное программирование».\nВариант 3.");
    }

    @FXML private void handleExit() {
        System.exit(0);
    }

    private void showAlert(String title, String header, String content) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(header);
        alert.setContentText(content);
        alert.showAndWait();
    }

    private void loadDemoData() {
        masterData.add(new Transaction("1", "Доход", "Зарплата", 450000.0, LocalDate.now().minusDays(15), "Оклад за месяц"));
        masterData.add(new Transaction("2", "Расход", "Продукты", 25000.0, LocalDate.now().minusDays(14), "Супермаркет Magnum"));
        masterData.add(new Transaction("3", "Расход", "Транспорт", 8000.0, LocalDate.now().minusDays(12), "Пополнение Onay"));
        masterData.add(new Transaction("4", "Расход", "Коммунальные", 35000.0, LocalDate.now().minusDays(10), "Оплата Квитанции"));
        masterData.add(new Transaction("5", "Доход", "Фриланс", 85000.0, LocalDate.now().minusDays(8), "Разработка верстки"));
        masterData.add(new Transaction("6", "Расход", "Развлечения", 12000.0, LocalDate.now().minusDays(7), "Поход в кино"));
        masterData.add(new Transaction("7", "Расход", "Кафе", 15000.0, LocalDate.now().minusDays(5), "Ужин с друзьями"));
        masterData.add(new Transaction("8", "Расход", "Здоровье", 9500.0, LocalDate.now().minusDays(4), "Покупка лекарств"));
        masterData.add(new Transaction("9", "Доход", "Кэшбэк", 3200.0, LocalDate.now().minusDays(2), "Возврат по карте"));
        masterData.add(new Transaction("10", "Расход", "Одежда", 42000.0, LocalDate.now().minusDays(1), "Покупка куртки"));
    }
}