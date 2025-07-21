function filterApplications() {
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

function updateStatus(applicationId) {
  document.getElementById("applicationId").value = applicationId
  document.getElementById("statusModal").classList.add("active")
}

function closeStatusModal() {
  document.getElementById("statusModal").classList.remove("active")
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

// Status form submission
document.getElementById("statusForm").addEventListener("submit", function (e) {
  e.preventDefault()

  const formData = new FormData(this)

  fetch("../Query/update_application_status.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Status updated successfully!")
        location.reload()
      } else {
        alert("Error: " + data.message)
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("An error occurred. Please try again.")
    })
})

// Close modal when clicking outside
window.onclick = (event) => {
  const modal = document.getElementById("statusModal")
  if (event.target === modal) {
    closeStatusModal()
  }
}
