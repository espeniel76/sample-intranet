# 멀티 스테이지 빌드
# 1단계: Maven 빌드 스테이지
FROM maven:3.9.4-eclipse-temurin-17 AS builder

# 작업 디렉토리 설정
WORKDIR /app

# Maven 설정 파일 복사
COPY pom.xml .

# 의존성 다운로드 (캐시 최적화)
RUN mvn dependency:go-offline -B

# 소스 코드 복사
COPY src ./src

# 애플리케이션 빌드
RUN mvn clean package -DskipTests

# 2단계: 실행 스테이지
FROM eclipse-temurin:17-jre-alpine

# 작업 디렉토리 설정
WORKDIR /app

# 빌드된 JAR 파일 복사
COPY --from=builder /app/target/*.jar app.jar

# 포트 노출
EXPOSE 9090

# 애플리케이션 실행
CMD ["java", "-jar", "app.jar"] 