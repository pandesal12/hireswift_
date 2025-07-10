let skillTags = [];
let educationTags = [];

function openJobModal(jobId = null) {
    const modal = document.getElementById('jobModal');
    const title = document.getElementById('modalTitle');
    
    if (jobId) {
        title.textContent = 'Edit Job';
        console.log(jobId);
        loadJobData(jobId);
    } else {
        title.textContent = 'Add New Job';
        document.getElementById('jobForm').reset();
        document.getElementById('jobId').value = '';
        skillTags = [];
        educationTags = [];
        updateTagsDisplay();
    }
    
    modal.classList.add('active');
}

function closeJobModal() {
    document.getElementById('jobModal').classList.remove('active');
}

function addSkillTag(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        const input = event.target;
        const value = input.value.trim();
        
        if (value && !skillTags.includes(value)) {
            skillTags.push(value);
            input.value = '';
            updateSkillsDisplay();
        }
    }
}

function addEducationTag(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        const input = event.target;
        const value = input.value.trim();
        
        if (value && !educationTags.includes(value)) {
            educationTags.push(value);
            input.value = '';
            updateEducationDisplay();
        }
    }
}

function removeSkillTag(index) {
    skillTags.splice(index, 1);
    updateSkillsDisplay();
}

function removeEducationTag(index) {
    educationTags.splice(index, 1);
    updateEducationDisplay();
}

function updateSkillsDisplay() {
    const container = document.getElementById('skillsInput');
    const input = container.querySelector('.tag-input');
    
    container.querySelectorAll('.tag').forEach(tag => tag.remove());
    
    skillTags.forEach((skill, index) => {
        const tag = document.createElement('div');
        tag.className = 'tag';
        tag.innerHTML = `
            ${skill}
            <button type="button" class="tag-remove" onclick="removeSkillTag(${index})">&times;</button>
        `;
        container.insertBefore(tag, input);
    });
    
    document.getElementById('skillsHidden').value = JSON.stringify(skillTags);
}

function updateEducationDisplay() {
    const container = document.getElementById('educationInput');
    const input = container.querySelector('.tag-input');
    
    container.querySelectorAll('.tag').forEach(tag => tag.remove());
    
    educationTags.forEach((education, index) => {
        const tag = document.createElement('div');
        tag.className = 'tag';
        tag.innerHTML = `
            ${education}
            <button type="button" class="tag-remove" onclick="removeEducationTag(${index})">&times;</button>
        `;
        container.insertBefore(tag, input);
    });
    
    document.getElementById('educationHidden').value = JSON.stringify(educationTags);
}

function updateTagsDisplay() {
    updateSkillsDisplay();
    updateEducationDisplay();
}

function editJob(jobId) {
    openJobModal(jobId);
}

function deleteJob(jobId) {
    if (confirm('Are you sure you want to delete this job? This action cannot be undone.')) {
        window.location.href = `../Query/delete_job.php?id=${jobId}`;
    }
}

function shareJob(jobId){
    function encryptNumber(num) {
        const numStr = num.toString();
        const secret = 123; // XOR key (must be <= 255)

        const byteArr = [];
        for (let i = 0; i < numStr.length; i++) {
            const xorByte = numStr.charCodeAt(i) ^ secret;
            byteArr.push(xorByte);
        }

        // Convert byte array to binary string
        const binaryStr = String.fromCharCode(...byteArr);
        return btoa(binaryStr); // Base64 encode
    }
    number = jobId;

    encrypted = encodeURIComponent(encryptNumber(number));
    
    //TODO: change later to non-localhost url
    navigator.clipboard.writeText("http://localhost/hireswift_/Temporary/upload_test.php?jobId="+encrypted)
    alert("Link to resume upload page for job copied to clipboard.")
}

function loadJobData(jobId) {
    fetch(`../Query/get_job.php?id=${jobId}`)
        .then(response => response.json())
        .then(job => {
            if (job.error) {
                alert('Error loading job: ' + job.error);
                return;
            }
            
            document.getElementById('jobId').value = job.id;
            document.getElementById('jobTitle').value = job.title;
            document.getElementById('employmentType').value = job.employment_type;
            document.getElementById('jobDescription').value = job.description;
            
            skillTags = job.skills || [];
            educationTags = job.education || [];
            updateTagsDisplay();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading job data');
        });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('jobModal');
    if (event.target === modal) {
        closeJobModal();
    }
}