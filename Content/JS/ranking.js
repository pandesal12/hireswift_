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

  // Handle path - remove any leading ../ and ensure it starts from root
  let pdfUrl = resumePath
  if (pdfUrl.startsWith("../")) {
    pdfUrl = pdfUrl.substring(3) // Remove ../
  }
  if (!pdfUrl.startsWith("/")) {
    pdfUrl = "../" + pdfUrl // Add ../ for navigation from Content folder
  }

  window.open(pdfUrl, "_blank")
}

function deleteApplication(applicationId) {
  if (
    confirm(
      "Are you sure you want to delete this application? This will permanently delete the application, resume file, and parsed data. This action cannot be undone.",
    )
  ) {
    // Show loading state
    const deleteBtn = event.target.closest("button")
    const originalContent = deleteBtn.innerHTML
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'
    deleteBtn.disabled = true

    fetch("../Query/delete_application.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ application_id: applicationId }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Application deleted successfully!")
          location.reload()
        } else {
          alert("Error: " + data.message)
          deleteBtn.innerHTML = originalContent
          deleteBtn.disabled = false
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("An error occurred. Please try again.")
        deleteBtn.innerHTML = originalContent
        deleteBtn.disabled = false
      })
  }
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
        // Store job requirements globally for comparison
        window.currentJobRequirements = data.job_requirements || { skills: [], education: [] }
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

  // Skills with matching analysis
  if (resume.Skills && resume.Skills.length > 0) {
    const jobSkills = window.currentJobRequirements?.skills || []
    const resumeSkills = resume.Skills || []

    // Categorize skills
    const matchedSkills = []
    const extraSkills = []
    const missingSkills = []

    // Find matched and extra skills
    resumeSkills.forEach((skill) => {
      const isMatched = jobSkills.some(
        (jobSkill) =>
          jobSkill.toLowerCase().includes(skill.toLowerCase()) || skill.toLowerCase().includes(jobSkill.toLowerCase()),
      )

      if (isMatched) {
        matchedSkills.push(skill)
      } else {
        extraSkills.push(skill)
      }
    })

    // Find missing skills (required but not in resume)
    jobSkills.forEach((jobSkill) => {
      const isPresent = resumeSkills.some(
        (skill) =>
          jobSkill.toLowerCase().includes(skill.toLowerCase()) || skill.toLowerCase().includes(jobSkill.toLowerCase()),
      )

      if (!isPresent) {
        missingSkills.push(jobSkill)
      }
    })

    html += `
            <div class="resume-section">
                <h3><i class="fas fa-cogs"></i> Skills Analysis</h3>
                <div class="skills-stats">
                    <span class="stat-item matched">
                        <i class="fas fa-check-circle"></i>
                        ${matchedSkills.length} Matched
                    </span>
                    <span class="stat-item missing">
                        <i class="fas fa-times-circle"></i>
                        ${missingSkills.length} Missing
                    </span>
                    <span class="stat-item extra">
                        <i class="fas fa-plus-circle"></i>
                        ${extraSkills.length} Additional
                    </span>
                </div>
                <div class="skills-list">
        `

    // Display matched skills (green)
    matchedSkills.forEach((skill) => {
      html += `<span class="skill-tag matched">${skill}</span>`
    })

    // Display extra skills (blue)
    extraSkills.forEach((skill) => {
      html += `<span class="skill-tag extra">${skill}</span>`
    })

    // Display missing skills (red)
    missingSkills.forEach((skill) => {
      html += `<span class="skill-tag missing">${skill}</span>`
    })

    html += `
                </div>
            </div>
        `
  }

  // Education with matching analysis
  if (resume.Education && resume.Education.length > 0) {
    const jobEducation = window.currentJobRequirements?.education || []
    const resumeEducation = resume.Education || []

    // Categorize education
    const matchedEducation = []
    const extraEducation = []
    const missingEducation = []

    // Find matched and extra education
    resumeEducation.forEach((edu) => {
      const isMatched = jobEducation.some(
        (jobEdu) =>
          jobEdu.toLowerCase().includes(edu.toLowerCase()) || edu.toLowerCase().includes(jobEdu.toLowerCase()),
      )

      if (isMatched) {
        matchedEducation.push(edu)
      } else {
        extraEducation.push(edu)
      }
    })

    // Find missing education (required but not in resume)
    jobEducation.forEach((jobEdu) => {
      const isPresent = resumeEducation.some(
        (edu) => jobEdu.toLowerCase().includes(edu.toLowerCase()) || edu.toLowerCase().includes(jobEdu.toLowerCase()),
      )

      if (!isPresent) {
        missingEducation.push(jobEdu)
      }
    })

    html += `
            <div class="resume-section">
                <h3><i class="fas fa-graduation-cap"></i> Education Analysis</h3>
                <div class="skills-stats">
                    <span class="stat-item matched">
                        <i class="fas fa-check-circle"></i>
                        ${matchedEducation.length} Matched
                    </span>
                    <span class="stat-item missing">
                        <i class="fas fa-times-circle"></i>
                        ${missingEducation.length} Missing
                    </span>
                    <span class="stat-item extra">
                        <i class="fas fa-plus-circle"></i>
                        ${extraEducation.length} Additional
                    </span>
                </div>
                <div class="education-list">
        `

    // Display matched education (green)
    matchedEducation.forEach((edu) => {
      html += `<span class="education-tag matched">${edu}</span>`
    })

    // Display extra education (blue)
    extraEducation.forEach((edu) => {
      html += `<span class="education-tag extra">${edu}</span>`
    })

    // Display missing education (red)
    missingEducation.forEach((edu) => {
      html += `<span class="education-tag missing">${edu}</span>`
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
