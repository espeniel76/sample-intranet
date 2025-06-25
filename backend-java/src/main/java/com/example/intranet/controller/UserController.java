package com.example.intranet.controller;

import com.example.intranet.dto.*;
import com.example.intranet.entity.User;
import com.example.intranet.service.UserService;
import com.example.intranet.util.JwtUtil;
import jakarta.validation.Valid;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Map;
import java.util.Optional;

/**
 * 사용자 관리 REST API 컨트롤러
 * Go, Python 백엔드와 동일한 API 스펙 제공
 */
@RestController
@RequestMapping("/api/v1")
@CrossOrigin(origins = "*")
public class UserController {

    private final UserService userService;
    private final JwtUtil jwtUtil;

    @Autowired
    public UserController(UserService userService, JwtUtil jwtUtil) {
        this.userService = userService;
        this.jwtUtil = jwtUtil;
    }

    /**
     * 헬스체크 엔드포인트
     */
    @GetMapping("/health")
    public ResponseEntity<Map<String, Object>> healthCheck() {
        return ResponseEntity.ok(Map.of(
                "status", "healthy",
                "timestamp", System.currentTimeMillis() / 1000,
                "app_name", "Sample Intranet Backend Java"
        ));
    }

    /**
     * 회원가입
     */
    @PostMapping("/auth/register")
    public ResponseEntity<?> register(@Valid @RequestBody UserCreateRequest request) {
        try {
            UserResponse userResponse = userService.createUser(request);
            return ResponseEntity.status(HttpStatus.CREATED).body(userResponse);
        } catch (RuntimeException e) {
            return ResponseEntity.badRequest().body(Map.of(
                    "error", "회원가입 실패",
                    "detail", e.getMessage()
            ));
        }
    }

    /**
     * 로그인
     */
    @PostMapping("/auth/login")
    public ResponseEntity<?> login(@Valid @RequestBody UserLoginRequest request) {
        try {
            Optional<User> userOpt = userService.authenticateUser(request.getEmail(), request.getPassword());
            
            if (userOpt.isPresent()) {
                User user = userOpt.get();
                String token = jwtUtil.generateToken(user.getEmail(), user.getId());
                
                JwtResponse response = new JwtResponse(
                        token,
                        jwtUtil.getExpirationInSeconds(),
                        UserResponse.from(user)
                );
                
                return ResponseEntity.ok(response);
            } else {
                return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(Map.of(
                        "error", "로그인 실패",
                        "detail", "이메일 또는 비밀번호가 올바르지 않습니다"
                ));
            }
        } catch (Exception e) {
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(Map.of(
                    "error", "로그인 처리 중 오류 발생",
                    "detail", e.getMessage()
            ));
        }
    }

    /**
     * 사용자 목록 조회 (인증 필요)
     */
    @GetMapping("/users")
    public ResponseEntity<List<UserResponse>> getUsers(
            @RequestParam(defaultValue = "0") int skip,
            @RequestParam(defaultValue = "100") int limit) {
        // TODO: JWT 인증 미들웨어 추가 필요
        List<UserResponse> users = userService.getAllUsers();
        return ResponseEntity.ok(users);
    }

    /**
     * 특정 사용자 조회 (인증 필요)
     */
    @GetMapping("/users/{id}")
    public ResponseEntity<?> getUser(@PathVariable Long id) {
        try {
            UserResponse user = userService.getUserById(id);
            return ResponseEntity.ok(user);
        } catch (RuntimeException e) {
            return ResponseEntity.notFound().build();
        }
    }

    /**
     * 사용자 정보 수정 (인증 필요)
     */
    @PutMapping("/users/{id}")
    public ResponseEntity<?> updateUser(
            @PathVariable Long id,
            @RequestBody Map<String, Object> updates) {
        try {
            // TODO: 권한 체크 (본인 또는 관리자만)
            String name = (String) updates.get("name");
            String roleStr = (String) updates.get("role");
            Boolean isActive = (Boolean) updates.get("isActive");
            
            UserResponse updated = userService.updateUser(id, name, 
                    roleStr != null ? com.example.intranet.entity.UserRole.valueOf(roleStr.toUpperCase()) : null, 
                    isActive);
            
            return ResponseEntity.ok(updated);
        } catch (RuntimeException e) {
            return ResponseEntity.badRequest().body(Map.of(
                    "error", "사용자 정보 수정 실패",
                    "detail", e.getMessage()
            ));
        }
    }

    /**
     * 사용자 삭제 (관리자 전용)
     */
    @DeleteMapping("/admin/users/{id}")
    public ResponseEntity<?> deleteUser(@PathVariable Long id) {
        try {
            // TODO: 관리자 권한 체크
            userService.deleteUser(id);
            return ResponseEntity.ok(Map.of(
                    "message", "사용자가 삭제되었습니다"
            ));
        } catch (RuntimeException e) {
            return ResponseEntity.badRequest().body(Map.of(
                    "error", "사용자 삭제 실패",
                    "detail", e.getMessage()
            ));
        }
    }

    /**
     * 사용자 검색
     */
    @GetMapping("/users/search")
    public ResponseEntity<List<UserResponse>> searchUsers(@RequestParam String name) {
        List<UserResponse> users = userService.searchUsersByName(name);
        return ResponseEntity.ok(users);
    }
} 