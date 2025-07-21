let skillTags = []
let educationTags = []

function openJobModal(jobId = null) {
  const modal = document.getElementById("jobModal")
  const title = document.getElementById("modalTitle")

  if (jobId) {
    title.textContent = "Edit Job"
    console.log("Loading job ID:", jobId)
    loadJobData(jobId)
  } else {
    title.textContent = "Add New Job"
    document.getElementById("jobForm").reset()
    document.getElementById("jobId").value = ""
    document.getElementById("jobStatus").value = "Active" // Default to Active for new jobs
    skillTags = []
    educationTags = []
    updateTagsDisplay()
  }

  modal.classList.add("active")
}

function closeJobModal() {
  document.getElementById("jobModal").classList.remove("active")
}

function addSkillTag(event) {
  if (event.key === "Enter") {
    event.preventDefault()
    const input = event.target
    const value = input.value.trim()

    if (value && !skillTags.includes(value)) {
      skillTags.push(value)
      input.value = ""
      updateSkillsDisplay()
    }
  }
}

function addEducationTag(event) {
  if (event.key === "Enter") {
    event.preventDefault()
    const input = event.target
    const value = input.value.trim()

    if (value && !educationTags.includes(value)) {
      educationTags.push(value)
      input.value = ""
      updateEducationDisplay()
    }
  }
}

function removeSkillTag(index) {
  skillTags.splice(index, 1)
  updateSkillsDisplay()
}

function removeEducationTag(index) {
  educationTags.splice(index, 1)
  updateEducationDisplay()
}

function updateSkillsDisplay() {
  const container = document.getElementById("skillsInput")
  const input = container.querySelector(".tag-input")

  container.querySelectorAll(".tag").forEach((tag) => tag.remove())

  skillTags.forEach((skill, index) => {
    const tag = document.createElement("div")
    tag.className = "tag"
    tag.innerHTML = `
            ${skill}
            <button type="button" class="tag-remove" onclick="removeSkillTag(${index})">&times;</button>
        `
    container.insertBefore(tag, input)
  })

  document.getElementById("skillsHidden").value = JSON.stringify(skillTags)
}

function updateEducationDisplay() {
  const container = document.getElementById("educationInput")
  const input = container.querySelector(".tag-input")

  container.querySelectorAll(".tag").forEach((tag) => tag.remove())

  educationTags.forEach((education, index) => {
    const tag = document.createElement("div")
    tag.className = "tag"
    tag.innerHTML = `
            ${education}
            <button type="button" class="tag-remove" onclick="removeEducationTag(${index})">&times;</button>
        `
    container.insertBefore(tag, input)
  })

  document.getElementById("educationHidden").value = JSON.stringify(educationTags)
}

function updateTagsDisplay() {
  updateSkillsDisplay()
  updateEducationDisplay()
}

function editJob(jobId) {
  openJobModal(jobId)
}

function deleteJob(jobId) {
  if (confirm("Are you sure you want to delete this job? This action cannot be undone.")) {
    // Show loading state
    const deleteBtn = event.target.closest("button")
    const originalContent = deleteBtn.innerHTML
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'
    deleteBtn.disabled = true

    // Redirect to delete script
    window.location.href = `../Query/delete_job.php?id=${jobId}`
  }
}

function shareJob(jobId) {
  function encryptNumber(num) {
    const numStr = num.toString()
    const secret = 123 // XOR key (must be <= 255)

    const byteArr = []
    for (let i = 0; i < numStr.length; i++) {
      const xorByte = numStr.charCodeAt(i) ^ secret
      byteArr.push(xorByte)
    }

    // Convert byte array to binary string
    const binaryStr = String.fromCharCode(...byteArr)
    return btoa(binaryStr) // Base64 encode
  }

  const encrypted = encodeURIComponent(encryptNumber(jobId))

  // TODO: change later to non-localhost url
  const shareUrl = `http://localhost/hireswift_/Forms/index.php?link=${encodeURIComponent(getCompanyName())}`

  navigator.clipboard
    .writeText(shareUrl)
    .then(() => {
      alert("Company application link copied to clipboard.")
    })
    .catch((err) => {
      console.error("Failed to copy: ", err)
      // Fallback: show the URL in a prompt
      prompt("Copy this link:", shareUrl)
    })
}

function getCompanyName() {
  // This should be dynamically set based on the current user's company
  // For now, we'll extract it from the page or use a global variable
  // You might want to pass this from PHP or store it in a data attribute
  return "<?php echo isset($companyName) ? $companyName : 'your-company'; ?>"
}

function loadJobData(jobId) {
  console.log("Fetching job data for ID:", jobId)

  fetch(`../Query/get_job.php?id=${jobId}`)
    .then((response) => {
      console.log("Response status:", response.status)
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((job) => {
      console.log("Job data received:", job)

      if (job.error) {
        alert("Error loading job: " + job.error)
        return
      }

      document.getElementById("jobId").value = job.id
      document.getElementById("jobTitle").value = job.title
      document.getElementById("employmentType").value = job.employment_type
      document.getElementById("jobStatus").value = job.status || "Active"
      document.getElementById("jobDescription").value = job.description

      skillTags = job.skills || []
      educationTags = job.education || []
      updateTagsDisplay()
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("Error loading job data: " + error.message)
    })
}

// Close modal when clicking outside
window.onclick = (event) => {
  const modal = document.getElementById("jobModal")
  if (event.target === modal) {
    closeJobModal()
  }
}

// Form submission handler
document.getElementById("jobForm").addEventListener("submit", function (e) {
  // Update hidden fields before submission
  updateTagsDisplay()

  // Show loading state
  const submitBtn = this.querySelector('button[type="submit"]')
  const originalContent = submitBtn.innerHTML
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'
  submitBtn.disabled = true

  // Let the form submit normally
  // The loading state will be cleared when the page reloads
})
