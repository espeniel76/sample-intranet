package com.example.intranet.service;

import com.example.intranet.dto.UserCreateRequest;
import com.example.intranet.dto.UserResponse;
import com.example.intranet.entity.User;
import com.example.intranet.entity.UserRole;
import com.example.intranet.repository.UserRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;
import java.util.Optional;
import java.util.stream.Collectors;

/**
 * 사용자 서비스
 * 비즈니스 로직 처리
 */
@Service
@Transactional
public class UserService {

    private final UserRepository userRepository;
    private final PasswordEncoder passwordEncoder;

    @Autowired
    public UserService(UserRepository userRepository, PasswordEncoder passwordEncoder) {
        this.userRepository = userRepository;
        this.passwordEncoder = passwordEncoder;
    }

    /**
     * 사용자 생성 (회원가입)
     */
    public UserResponse createUser(UserCreateRequest request) {
        // 이메일 중복 체크
        if (userRepository.existsByEmail(request.getEmail())) {
            throw new RuntimeException("이미 존재하는 이메일입니다: " + request.getEmail());
        }

        // 새 사용자 생성
        User user = new User();
        user.setEmail(request.getEmail());
        user.setPassword(passwordEncoder.encode(request.getPassword())); // 비밀번호 암호화
        user.setName(request.getName());
        user.setRole(request.getRole() != null ? request.getRole() : UserRole.USER);
        user.setIsActive(request.getIsActive() != null ? request.getIsActive() : true);

        User savedUser = userRepository.save(user);
        return UserResponse.from(savedUser);
    }

    /**
     * 모든 사용자 조회
     */
    @Transactional(readOnly = true)
    public List<UserResponse> getAllUsers() {
        return userRepository.findAllActiveUsers()
                .stream()
                .map(UserResponse::from)
                .collect(Collectors.toList());
    }

    /**
     * ID로 사용자 조회
     */
    @Transactional(readOnly = true)
    public UserResponse getUserById(Long id) {
        User user = userRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("사용자를 찾을 수 없습니다: " + id));
        return UserResponse.from(user);
    }

    /**
     * 이메일로 사용자 조회
     */
    @Transactional(readOnly = true)
    public Optional<User> getUserByEmail(String email) {
        return userRepository.findByEmail(email);
    }

    /**
     * 사용자 인증 (로그인)
     */
    @Transactional(readOnly = true)
    public Optional<User> authenticateUser(String email, String password) {
        Optional<User> userOpt = userRepository.findByEmailAndIsActive(email, true);
        
        if (userOpt.isPresent()) {
            User user = userOpt.get();
            if (passwordEncoder.matches(password, user.getPassword())) {
                return Optional.of(user);
            }
        }
        return Optional.empty();
    }

    /**
     * 사용자 정보 업데이트
     */
    public UserResponse updateUser(Long id, String name, UserRole role, Boolean isActive) {
        User user = userRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("사용자를 찾을 수 없습니다: " + id));

        if (name != null) {
            user.setName(name);
        }
        if (role != null) {
            user.setRole(role);
        }
        if (isActive != null) {
            user.setIsActive(isActive);
        }

        User updatedUser = userRepository.save(user);
        return UserResponse.from(updatedUser);
    }

    /**
     * 사용자 삭제
     */
    public void deleteUser(Long id) {
        User user = userRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("사용자를 찾을 수 없습니다: " + id));
        
        userRepository.delete(user);
    }

    /**
     * 사용자 이름으로 검색
     */
    @Transactional(readOnly = true)
    public List<UserResponse> searchUsersByName(String name) {
        return userRepository.findByNameContaining(name)
                .stream()
                .map(UserResponse::from)
                .collect(Collectors.toList());
    }
} 