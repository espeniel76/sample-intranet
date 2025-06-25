package com.example.intranet.entity;

/**
 * 사용자 역할 열거형
 * Go, Python 백엔드와 동일한 역할 구조
 */
public enum UserRole {
    USER("user"),
    ADMIN("admin");

    private final String value;

    UserRole(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }

    @Override
    public String toString() {
        return value;
    }

    /**
     * 문자열 값으로부터 UserRole을 찾아 반환
     */
    public static UserRole fromValue(String value) {
        for (UserRole role : UserRole.values()) {
            if (role.value.equals(value)) {
                return role;
            }
        }
        throw new IllegalArgumentException("Unknown role: " + value);
    }
} 