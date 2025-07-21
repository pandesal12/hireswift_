#!/usr/bin/env python3
import sys
import os
import json
import mysql.connector
from pdfminer.high_level import extract_text
import re
import spacy
from openai import OpenAI

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
        return mysql.connector.connect(**DB_CONFIG)
    except mysql.connector.Error as err:
        print(f"Database connection error: {err}")
        return None

def get_job_requirements(application_id):
    """Get job requirements from database"""
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
            return skills, education
        
        return [], []
        
    except mysql.connector.Error as err:
        print(f"Database query error: {err}")
        return [], []
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def extract_text_from_pdf(pdf_path):
    """Extract text from PDF file"""
    try:
        return extract_text(pdf_path)
    except Exception as e:
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

def extract_name(text):
    """Extract name using spaCy NLP"""
    try:
        nlp = spacy.load('en_core_web_sm')
        doc = nlp(text)
        for ent in doc.ents:
            if ent.label_ == "PERSON":
                return ent.text
        return None
    except Exception as e:
        print(f"Error in name extraction: {e}")
        return None

def extract_skills(text, job_skills=None):
    """Extract skills from text based on job requirements"""
    if job_skills is None:
        job_skills = ['Python', 'SQL', 'Excel', 'Power BI', 'Machine Learning', 'Data Analysis', 
                     'JavaScript', 'PHP', 'Java', 'C++', 'HTML', 'CSS', 'React', 'Node.js']
    
    found = [skill for skill in job_skills if skill.lower() in text.lower()]
    return list(set(found))

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
            return match.group(1)
    
    return 'Not Found'

def calculate_score(found_skills, job_skills):
    """Calculate matching score based on skills found vs required"""
    if not job_skills:
        return 0.0
    
    matches = len(found_skills)
    total = len(job_skills)
    
    if total == 0:
        return 0.0
    
    score = (matches / total) * 100
    return round(score, 2)


def update_application_in_db(application_id, parsed_data, parsed_file_path, score):
    """Update application record with parsed data and score"""
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
            parsed_file_path,
            parsed_data.get('Name', ''),
            parsed_data.get('Email', ''),
            parsed_data.get('Phone', ''),
            score,
            application_id
        ))
        
        conn.commit()
        return True
        
    except mysql.connector.Error as err:
        print(f"Database update error: {err}")
        return False
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def main():
    """Main function"""
    if len(sys.argv) < 3:
        print("Usage: python NLP.py <pdf_path> <application_id>")
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    application_id = int(sys.argv[2])
    
    print(f"Processing PDF: {pdf_path}")
    print(f"Application ID: {application_id}")
    
    # Check if file exists
    if not os.path.exists(pdf_path):
        print(f"Error: File {pdf_path} does not exist")
        sys.exit(1)
    
    # Get job requirements from database
    job_skills, job_education = get_job_requirements(application_id)
    print(f"Job skills required: {job_skills}")
    print(f"Job education required: {job_education}")
    
    # Extract text from PDF
    raw_text = extract_text_from_pdf(pdf_path)
    if not raw_text:
        print("Error: Could not extract text from PDF")
        sys.exit(1)
    
    # Clean text
    cleaned_text = clean_text(raw_text)
    
    # Extract information using job-specific requirements
    found_skills = extract_skills(cleaned_text, job_skills)
    score = calculate_score(found_skills, job_skills)
    
    parsed_resume = {
        'Name': extract_name(cleaned_text),
        'Email': extract_email(cleaned_text),
        'Phone': extract_phone(cleaned_text),
        'Skills': found_skills,
        'Education': extract_education(cleaned_text, job_education),
        'Experience': extract_experience(cleaned_text),
        'GPA': extract_gpa(cleaned_text),
        'Score': score
    }
    
    # Create parsed directory if it doesn't exist
    parsed_dir = os.path.join(os.path.dirname(pdf_path), 'parsed')
    if not os.path.exists(parsed_dir):
        os.makedirs(parsed_dir)
    
    # Generate output filename
    base_name = os.path.splitext(os.path.basename(pdf_path))[0]
    json_filename = f"{base_name}_parsed.json"
    json_path = os.path.join(parsed_dir, json_filename)
    
    # Save parsed data to JSON file
    try:
        with open(json_path, 'w') as json_file:
            json.dump(parsed_resume, json_file, indent=4)
        print(f"Parsed data saved to: {json_path}")
    except Exception as e:
        print(f"Error saving JSON file: {e}")
        sys.exit(1)
    
    # Update database with score
    if update_application_in_db(application_id, parsed_resume, json_path, score):
        print("Database updated successfully")
        print(f"Matching score: {score}%")
    else:
        print("Error updating database")
    
    # Output JSON for immediate use
    print("=== PARSED RESUME DATA ===")
    print(json.dumps(parsed_resume, indent=4))

if __name__ == "__main__":
    main()
