function filterRankings() {
  const jobId = document.getElementById("jobFilter").value
  const currentUrl = new URL(window.location)

  if (jobId === "0") {
    currentUrl.searchParams.delete("job_id")
  } else {
    currentUrl.searchParams.set("job_id", jobId)
  }

  window.location.href = currentUrl.toString()
}

function viewResume(resumePath) {
  if (!resumePath) {
    alert("Resume file not found")
    return
  }

  // Open PDF in new window
  const pdfUrl = "../" + resumePath
  window.open(pdfUrl, "_blank")
}

function viewParsedResume(applicationId) {
  document.getElementById("resumeModal").classList.add("active")

  // Show loading
  document.getElementById("resumeContent").innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            Loading resume analysis...
        </div>
    `

  // Fetch parsed resume data
  fetch("../Query/get_parsed_resume.php?id=" + applicationId)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayParsedResume(data.resume)
      } else {
        document.getElementById("resumeContent").innerHTML = `
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        ${data.message}
                    </div>
                `
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      document.getElementById("resumeContent").innerHTML = `
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    Error loading resume data
                </div>
            `
    })
}

function displayParsedResume(resume) {
  let html = ""

  // Basic Information
  html += `
        <div class="resume-section">
            <h3><i class="fas fa-user"></i> Personal Information</h3>
            <p><strong>Name:</strong> ${resume.Name || "Not specified"}</p>
            <p><strong>Email:</strong> ${resume.Email || "Not specified"}</p>
            <p><strong>Phone:</strong> ${resume.Phone || "Not specified"}</p>
            <p><strong>GPA:</strong> ${resume.GPA || "Not specified"}</p>
        </div>
    `

  // Skills
  if (resume.Skills && resume.Skills.length > 0) {
    html += `
            <div class="resume-section">
                <h3><i class="fas fa-cogs"></i> Skills</h3>
                <div class="skills-list">
        `
    resume.Skills.forEach((skill) => {
      html += `<span class="skill-tag">${skill}</span>`
    })
    html += `
                </div>
            </div>
        `
  }

  // Education
  if (resume.Education && resume.Education.length > 0) {
    html += `
            <div class="resume-section">
                <h3><i class="fas fa-graduation-cap"></i> Education</h3>
                <div class="education-list">
        `
    resume.Education.forEach((edu) => {
      html += `<span class="education-tag">${edu}</span>`
    })
    html += `
                </div>
            </div>
        `
  }

  // Experience
  if (resume.Experience && resume.Experience.length > 0) {
    html += `
            <div class="resume-section">
                <h3><i class="fas fa-briefcase"></i> Experience</h3>
                <div class="experience-list">
        `
    resume.Experience.forEach((exp) => {
      html += `<span class="experience-tag">${exp}</span>`
    })
    html += `
                </div>
            </div>
        `
  }

  // Summary
  if (resume.Summary) {
    html += `
            <div class="resume-section">
                <h3><i class="fas fa-file-text"></i> Summary</h3>
                <p>${resume.Summary}</p>
            </div>
        `
  }

  document.getElementById("resumeContent").innerHTML = html
}

function closeResumeModal() {
  document.getElementById("resumeModal").classList.remove("active")
}

// Close modal when clicking outside
window.onclick = (event) => {
  const modal = document.getElementById("resumeModal")
  if (event.target === modal) {
    closeResumeModal()
  }
}
