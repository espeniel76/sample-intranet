package com.example.intranet.dto;

/**
 * JWT 토큰 응답 DTO
 * Go, Python 백엔드와 동일한 응답 형식
 */
public class JwtResponse {

    private String accessToken;
    private String tokenType = "bearer";
    private Long expiresIn; // 만료 시간(초)
    private UserResponse user;

    // 기본 생성자
    public JwtResponse() {}

    // 생성자
    public JwtResponse(String accessToken, Long expiresIn, UserResponse user) {
        this.accessToken = accessToken;
        this.expiresIn = expiresIn;
        this.user = user;
    }

    // Getter & Setter
    public String getAccessToken() {
        return accessToken;
    }

    public void setAccessToken(String accessToken) {
        this.accessToken = accessToken;
    }

    public String getTokenType() {
        return tokenType;
    }

    public void setTokenType(String tokenType) {
        this.tokenType = tokenType;
    }

    public Long getExpiresIn() {
        return expiresIn;
    }

    public void setExpiresIn(Long expiresIn) {
        this.expiresIn = expiresIn;
    }

    public UserResponse getUser() {
        return user;
    }

    public void setUser(UserResponse user) {
        this.user = user;
    }
} 