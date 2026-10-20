package com.example.visual1.model;

import java.time.LocalDate;

public class Transaction {
    private String id;
    private String type; // "Доход" или "Расход"
    private String category;
    private double amount;
    private LocalDate date;
    private String description;

    public Transaction(String id, String type, String category, double amount, LocalDate date, String description) {
        this.id = id;
        this.type = type;
        this.category = category;
        this.amount = amount;
        this.date = date;
        this.description = description;
    }

    public String getId() { return id; }
    public void setId(String id) { this.id = id; }

    public String getType() { return type; }
    public void setType(String type) { this.type = type; }

    public String getCategory() { return category; }
    public void setCategory(String category) { this.category = category; }

    public double getAmount() { return amount; }
    public void setAmount(double amount) { this.amount = amount; }

    public LocalDate getDate() { return date; }
    public void setDate(LocalDate date) { this.date = date; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }
}