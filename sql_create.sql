-- Create applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    applicant_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    resume_pdf_path VARCHAR(255) NOT NULL COMMENT 'Path to the uploaded PDF resume',
    parsed_resume_path VARCHAR(255) COMMENT 'Path to the parsed resume JSON file',
    status ENUM('Pending', 'Reviewing', 'Shortlisted', 'Interviewed', 'Accepted', 'Rejected') DEFAULT 'Pending',
    score DECIMAL(5,2) DEFAULT 0 COMMENT 'Match score from 0-100',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_job_id (job_id),
    INDEX idx_status (status),
    INDEX idx_score (score),
    INDEX idx_created_at (created_at),
    CONSTRAINT fk_application_job_id FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
);

-- Insert dummy data
INSERT INTO applications (job_id, applicant_name, email, phone, resume_pdf_path, parsed_resume_path, status, score) VALUES
(
    1, -- Senior Software Engineer job
    'John Smith',
    'john.smith@example.com',
    '+1 (555) 123-4567',
    'uploads/resumes/john_smith_resume.pdf',
    'uploads/parsed/john_smith_parsed.json',
    'Shortlisted',
    87.5
)
