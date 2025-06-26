use std::env;

#[derive(Debug, Clone)]
pub struct Config {
    pub database_url: String,
    pub server_address: String,
    pub jwt_secret: String,
    pub jwt_expires_hours: u64,
}

impl Config {
    pub fn from_env() -> anyhow::Result<Self> {
        let database_url = env::var("DATABASE_URL")
            .unwrap_or_else(|_| "postgresql://postgres:password@localhost:5432/sample_intranet".to_string());
        
        let server_address = env::var("SERVER_ADDRESS")
            .unwrap_or_else(|_| "0.0.0.0:8070".to_string());
        
        let jwt_secret = env::var("JWT_SECRET")
            .unwrap_or_else(|_| "your-secret-key-change-in-production".to_string());
        
        let jwt_expires_hours = env::var("JWT_EXPIRES_HOURS")
            .unwrap_or_else(|_| "24".to_string())
            .parse::<u64>()
            .unwrap_or(24);

        Ok(Config {
            database_url,
            server_address,
            jwt_secret,
            jwt_expires_hours,
        })
    }
} 