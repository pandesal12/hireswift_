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

  // Open PDF in new window
  const pdfUrl = resumePath
  window.open(pdfUrl, "_blank")
}

function updateStatus(applicationId) {
  document.getElementById("applicationId").value = applicationId
  document.getElementById("statusModal").classList.add("active")
}

function closeStatusModal() {
  document.getElementById("statusModal").classList.remove("active")
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
