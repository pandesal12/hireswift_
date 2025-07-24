#!/usr/bin/env python3
import sys
import os
import json
import mysql.connector
from pdfminer.high_level import extract_text
import re
import spacy
from openai import OpenAI
from google import genai
from datetime import datetime
from dotenv import load_dotenv

load_dotenv()

# Create execution log at the very start
def log_execution(message):
    """Log execution steps to a file"""
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    log_message = f"{timestamp} - {message}\n"
    
    try:
        with open('../Temporary/python_execution.txt', 'a', encoding='utf-8') as f:
            f.write(log_message)
    except Exception as e:
        print(f"Failed to write to log: {e}")

# Log that the script has been called
log_execution("=== PYTHON SCRIPT STARTED ===")
log_execution(f"Script called with arguments: {sys.argv}")

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'hireswift'
}

def get_db_connection():
    """Get database connection"""
    try:
        log_execution("Attempting database connection...")
        conn = mysql.connector.connect(**DB_CONFIG)
        log_execution("Database connection successful")
        return conn
    except mysql.connector.Error as err:
        log_execution(f"Database connection error: {err}")
        print(f"Database connection error: {err}")
        return None

def get_job_requirements(application_id):
    """Get job requirements from database"""
    log_execution(f"Getting job requirements for application ID: {application_id}")
    
    conn = get_db_connection()
    if not conn:
        return None, None
    
    try:
        cursor = conn.cursor()
        query = """
        SELECT j.skills, j.education 
        FROM applications a 
        INNER JOIN jobs j ON a.job_id = j.id 
        WHERE a.id = %s
        """
        cursor.execute(query, (application_id,))
        result = cursor.fetchone()
        
        if result:
            skills = json.loads(result[0]) if result[0] else []
            education = json.loads(result[1]) if result[1] else []
            log_execution(f"Job requirements found - Skills: {len(skills)}, Education: {len(education)}")
            return skills, education
        
        log_execution("No job requirements found")
        return [], []
        
    except mysql.connector.Error as err:
        log_execution(f"Database query error: {err}")
        print(f"Database query error: {err}")
        return [], []
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def extract_text_from_pdf(pdf_path):
    """Extract text from PDF file"""
    log_execution(f"Extracting text from PDF: {pdf_path}")
    try:
        text = extract_text(pdf_path)
        log_execution(f"Text extraction successful, length: {len(text) if text else 0}")
        return text
    except Exception as e:
        log_execution(f"Error extracting text from PDF: {e}")
        print(f"Error extracting text from PDF: {e}")
        return None

def clean_text(text):
    """Clean extracted text"""
    if not text:
        return ""
    
    text = re.sub(r'\n+', '\n', text)
    text = re.sub(r' +', ' ', text)
    text = text.replace('\uf0a7', '')  # remove bullet
    return text.strip()

def extract_email(text):
    """Extract email from text"""
    match = re.search(r'\S+@\S+', text)
    return match.group(0) if match else None

def extract_phone(text):
    """Extract phone number from text"""
    match = re.search(r'\+?\d[\d\s\-()]{8,15}', text)
    return match.group(0) if match else None

#V2
def extract_name(text):
    client = genai.Client()
    response = client.models.generate_content(
        model="gemini-2.5-flash", contents=f"Im using this prompt as a function to identify the name and display it, I want you to say the name (Just say the name and nothing else from this text, its only 1 name). This is the extracted resume text: {text}"
    )
    return response.text

#V1
# def extract_skills(text, job_skills=None):
#     found = [skill for skill in job_skills if skill.lower() in text.lower()]
#     log_execution(f"Skills found: {found}")
#     return list(set(found))

def extract_skills(text, job_skills):
    client = genai.Client()
    response = client.models.generate_content(
        model="gemini-2.5-flash", contents=f"""Im using this prompt as a function to identify the skills and display it, 
        I want you to list all the skills in this manner (disregard the quotation marks) 'Skill1,Skill2,Skill3'. This is the extracted resume text: {text}"""
    )
    return response.text.split(',')

def extract_education(text, job_education=None):
    """Extract education information based on job requirements"""
    if job_education is None:
        job_education = ['Bachelor', 'Master', 'B.S', 'M.Sc', 'PhD', 'B.E', 'M.E', 'Degree']
    
    lines = text.split('\n')
    education = []
    
    # Check for job-specific education requirements
    for edu_req in job_education:
        for line in lines:
            if edu_req.lower() in line.lower():
                education.append(line.strip())
                break
    
    # Also check for general education keywords
    general_keywords = ['Bachelor', 'Master', 'B.S', 'M.Sc', 'PhD', 'B.E', 'M.E', 'Degree']
    for line in lines:
        for keyword in general_keywords:
            if keyword.lower() in line.lower() and line.strip() not in education:
                education.append(line.strip())
                break
    
    log_execution(f"Education found: {education}")
    return education

def extract_experience(text):
    """Extract experience information"""
    experience_keywords = ['experience', 'work', 'internship', 'employment', 'position', 'role']
    exp_lines = []
    lines = text.split("\n")
    
    for line in lines:
        for keyword in experience_keywords:
            if keyword.lower() in line.lower():
                exp_lines.append(line.strip())
                break
    
    # Filter out lines that are just the keywords themselves
    exp_lines = [line for line in exp_lines if line.strip().lower() not in experience_keywords]
    log_execution(f"Experience found: {len(exp_lines)} items")
    return exp_lines

def extract_gpa(text):
    """Extract GPA from text"""
    patterns = [
        r'gpa[:\s\-]*([0-4]\.\d{1,2})',
        r'grade point average[:\s\-]*([0-4]\.\d{1,2})',
        r'cumulative[:\s\-]*([0-4]\.\d{1,2})',
        r'([0-4]\.\d{1,2})\s*/\s*4\.?0*',
        r'([0-4]\.\d{1,2})\s*out of\s*4\.?0*',
    ]
    
    for pattern in patterns:
        match = re.search(pattern, text, re.IGNORECASE)
        if match:
            gpa = match.group(1)
            log_execution(f"GPA found: {gpa}")
            return gpa
    
    log_execution("No GPA found")
    return 'Not Found'

def calculate_score(found_skill_edu, requirement):
    if not requirement or not found_skill_edu:
        log_execution("No skills to compare. Score = 0.0")
        return 0.0
    matches = 0
    matched = []

    for edu_skill in requirement:
        edu_skill_lower = edu_skill.lower()
        for found in found_skill_edu:
            found_lower = found.lower()
            if edu_skill_lower in found_lower or found_lower in edu_skill_lower:
                matches += 1
                matched.append(edu_skill)
                break  # count each job_skill only once

    total = len(requirement)
    score = (matches / total) * 100
    log_execution(f"Score calculated: {score}% ({matches}/{total} skills matched): {matched}")
    return round(score, 2)

def generate_summary(text):
    
    client = genai.Client()
    response = client.models.generate_content(
        model="gemini-2.5-flash", contents=f"Give a short summary (ideally within 4-5 sentences while taking the significant information) do not include any formatting, you may exclude the skills only as this is already handled in a different script. This is the extracted resume text: {text}"
    )
    return response.text

def update_application_in_db(application_id, parsed_data, parsed_file_path, score):
    log_execution(f"Updating application {application_id} in database...")
    
    # Convert absolute path to relative path (without ../)
    # Find the Uploads folder and get everything from there
    path_parts = parsed_file_path.replace('\\', '/').split('/')
    
    try:
        uploads_index = next(i for i, part in enumerate(path_parts) if part.lower() == 'uploads')
        # Get everything from Uploads onwards
        relative_parts = path_parts[uploads_index:]
        relative_path = '/'.join(relative_parts)
        log_execution(f"Converted absolute path to relative: {parsed_file_path} -> {relative_path}")
    except StopIteration:
        # Fallback: just use the filename
        relative_path = os.path.basename(parsed_file_path)
        log_execution(f"Could not find Uploads folder, using filename: {relative_path}")
    
    conn = get_db_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        
        # Update application with parsed data and score
        update_query = """
        UPDATE applications 
        SET parsed_resume_path = %s,
            applicant_name = %s,
            email = %s,
            phone = %s,
            score = %s,
            updated_at = NOW()
        WHERE id = %s
        """
        
        cursor.execute(update_query, (
            relative_path,
            parsed_data.get('Name', ''),
            parsed_data.get('Email', ''),
            parsed_data.get('Phone', ''),
            score,
            application_id
        ))
        
        conn.commit()
        log_execution(f"Database update successful with relative path: {relative_path}")
        return True
        
    except mysql.connector.Error as err:
        log_execution(f"Database update error: {err}")
        print(f"Database update error: {err}")
        return False
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def main():
    """Main function"""
    log_execution("=== MAIN FUNCTION STARTED ===")
    
    if len(sys.argv) < 3:
        log_execution("ERROR: Insufficient arguments provided")
        print("Usage: python NLP.py <pdf_path> <application_id>")
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    application_id = int(sys.argv[2])
    
    log_execution(f"Processing PDF: {pdf_path}")
    log_execution(f"Application ID: {application_id}")
    
    print(f"Processing PDF: {pdf_path}")
    print(f"Application ID: {application_id}")
    
    # Check if file exists
    if not os.path.exists(pdf_path):
        log_execution(f"ERROR: File {pdf_path} does not exist")
        print(f"Error: File {pdf_path} does not exist")
        sys.exit(1)
    
    # Get job requirements from database
    job_skills, job_education = get_job_requirements(application_id)
    log_execution(f"Job skills required: {job_skills}")
    log_execution(f"Job education required: {job_education}")
    
    # Extract text from PDF
    raw_text = extract_text_from_pdf(pdf_path)
    if not raw_text:
        log_execution("ERROR: Could not extract text from PDF")
        print("Error: Could not extract text from PDF")
        sys.exit(1)
    
    # Clean text
    cleaned_text = clean_text(raw_text)
    log_execution(f"Text cleaned, final length: {len(cleaned_text)}")
    
    # Extract information using job-specific requirements
    found_skills = extract_skills(cleaned_text, job_skills)
    found_education = extract_education(cleaned_text, job_education)
    score = calculate_score(found_skills + found_education, job_skills + job_education)
    
    parsed_resume = {
        'Name': extract_name(cleaned_text),
        'Email': extract_email(cleaned_text),
        'Phone': extract_phone(cleaned_text),
        'Skills': found_skills,
        'Education': found_education,
        'Experience': extract_experience(cleaned_text),
        'GPA': extract_gpa(cleaned_text),
        'Summary': generate_summary(cleaned_text),
        'Score': score
    }
    
    log_execution(f"Resume parsing completed. Score: {score}%")
    
    # Create parsed directory if it doesn't exist
    parsed_dir = os.path.join(os.path.dirname(pdf_path), 'parsed')
    if not os.path.exists(parsed_dir):
        os.makedirs(parsed_dir)
        log_execution(f"Created parsed directory: {parsed_dir}")
    
    # Generate output filename
    base_name = os.path.splitext(os.path.basename(pdf_path))[0]
    json_filename = f"{base_name}_parsed.json"
    json_path = os.path.join(parsed_dir, json_filename)
    
    # Save parsed data to JSON file
    try:
        with open(json_path, 'w') as json_file:
            json.dump(parsed_resume, json_file, indent=4)
        log_execution(f"Parsed data saved to: {json_path}")
        print(f"Parsed data saved to: {json_path}")
    except Exception as e:
        log_execution(f"Error saving JSON file: {e}")
        print(f"Error saving JSON file: {e}")
        sys.exit(1)
    
    # Update database with score
    if update_application_in_db(application_id, parsed_resume, json_path, score):
        log_execution("Database updated successfully")
        log_execution(f"Final matching score: {score}%")
        print("Database updated successfully")
        print(f"Matching score: {score}%")
    else:
        log_execution("Error updating database")
        print("Error updating database")
    
    # Output JSON for immediate use
    print("=== PARSED RESUME DATA ===")
    print(json.dumps(parsed_resume, indent=4))
    
    log_execution("=== PYTHON SCRIPT COMPLETED SUCCESSFULLY ===")

if __name__ == "__main__":
    try:
        main()
    except Exception as e:
        log_execution(f"FATAL ERROR: {e}")
        print(f"Fatal error: {e}")
        sys.exit(1)
