#!pip install pdfminer.six
#!pip install spacy
#!python -m spacy download en_core_web_sm

from pdfminer.high_level import extract_text
import os

path = '../Python/resume/sample.pdf'

def extract_text_from_pdf(pdf_path):
    return extract_text(pdf_path)

sample_text = extract_text_from_pdf(path)
# print(sample_text[:1000])

import re
def clean_text(text):
    text = re.sub(r'\n+', '\n', text)
    text = re.sub(r' +', ' ', text)
    text = text.replace('\uf0a7', '') #remove bullet
    return text.strip()

cleaned = clean_text(sample_text)


def extract_email(text):
    match = re.search(r'\S+@\S+', text)
    return match.group(0) if match else None

def extract_phone(text):
    match = re.search(r'\+?\d[\d\s\-()]{8,15}', text)
    return match.group(0) if match else None

# email = extract_email(cleaned)
# phone = extract_phone(cleaned)
# print(f"Email: {email} | Phone: {phone}")

import spacy
nlp = spacy.load('en_core_web_sm')

def extract_name(text):
    doc = nlp(text)
    for ent in doc.ents:
        if ent.label_ == "PERSON":
            return ent.text
        return None
    
SKILL_SET = ['Python', 'SQL', 'Excel', 'Power BI', 'Machine Learning', 'Data Analysis']
def extract_skills(text, skills=SKILL_SET):
    found = [skill for skill in skills if skill.lower() in text.lower()]
    return list(set(found))

EDU_KEYWORDS = ['Bachelor', 'Master', 'B.S', 'M.Sc', 'PhD', 'B.E', 'M.E']
def extract_education(text, edu=EDU_KEYWORDS):
    lines = text.split('\n')
    education = []
    for line in lines:
        for word in edu:
            if word.lower() in line.lower():
                education.append(line.strip())
    return education

experience_set = ['experience', 'work', 'internship', 'employment']
def extract_experience(text, exp=experience_set):
    exp_lines = []
    lines = text.split("\n")
    for line in lines:
        for keyword in exp:
            if keyword.lower() in line.lower():
                exp_lines.append(line.strip())
    exp_lines = [line for line in exp_lines if line.strip().lower() not in experience_set]
    return exp_lines

def extract_gpa(text):
    # Common GPA patterns
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

#Summary Usage
from openai import OpenAI

client = OpenAI(
  base_url="https://openrouter.ai/api/v1",
  api_key="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",
)

completion = client.chat.completions.create(
  extra_headers={
  },
  extra_body={},
  model="deepseek/deepseek-r1-0528:free",
  messages=[
    {
      "role": "user",
      "content": f"Give a short summary (ideally within 4-5 sentences while taking the significant information) do not include any formatting, you may exclude the skills only as this is already handled in a different script. This is the extacted resume text: {cleaned}"
    }
  ]
)
summary = completion.choices[0].message.content

parsed_resume = {
    'Name': extract_name(cleaned),
    'Email': extract_email(cleaned),
    'Phone': extract_phone(cleaned),
    'Skills': extract_skills(cleaned),
    'Education': extract_education(cleaned),
    'Experience': extract_experience(cleaned),
    'GPA': extract_gpa(cleaned),
    'Summary': summary
}

import json
json_string = json.dumps(parsed_resume, indent=4)
print(json_string)

def get_output_filename(pdf_path):
    base_name = os.path.splitext(os.path.basename(pdf_path))[0]
    filename = f"{base_name}.json"
    counter = 2

    while os.path.exists(filename):
        filename = f"{base_name}-{counter}.json"
        counter += 1

    return filename

with open(f'../Python/parsed/{get_output_filename(path)}', 'w') as json_file:
    json.dump(parsed_resume, json_file, indent=4)
