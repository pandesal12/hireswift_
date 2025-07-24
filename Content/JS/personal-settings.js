// Store original values
const originalValues = {
  name: document.getElementById("fullName").value,
  email: document.getElementById("email").value,
  phone: document.getElementById("phone").value,
  company: document.getElementById("company").value,
}

// Email and company availability checking
let emailCheckTimeout
let companyCheckTimeout
let isEmailAvailable = false
let isCompanyAvailable = false

const emailInput = document.getElementById("email")
const emailStatusIcon = document.getElementById("emailStatusIcon")
const emailStatusMessage = document.getElementById("emailStatusMessage")
const companyInput = document.getElementById("company")
const companyStatusIcon = document.getElementById("companyStatusIcon")
const companyStatusMessage = document.getElementById("companyStatusMessage")
const updateBtn = document.getElementById("updateBtn")

// Form inputs
const nameInput = document.getElementById("fullName")
const phoneInput = document.getElementById("phone")

// Add event listeners to all form inputs
nameInput.addEventListener("input", checkFormChanges)
emailInput.addEventListener("input", handleEmailChange)
phoneInput.addEventListener("input", handlePhoneChange)
companyInput.addEventListener("input", handleCompanyChange)

// Copy Forms Link Function
function copyFormsLink() {
  const linkInput = document.getElementById("formsLink")
  const copyBtn = document.getElementById("copyLinkBtn")
  const copyText = copyBtn.querySelector(".copy-text")
  const copyIcon = copyBtn.querySelector("i")

  // Select and copy the text
  linkInput.select()
  linkInput.setSelectionRange(0, 99999) // For mobile devices

  try {
    // Try modern clipboard API first
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard
        .writeText(linkInput.value)
        .then(() => {
          showCopySuccess(copyBtn, copyIcon, copyText)
        })
        .catch(() => {
          // Fallback to execCommand
          fallbackCopy(linkInput, copyBtn, copyIcon, copyText)
        })
    } else {
      // Fallback to execCommand
      fallbackCopy(linkInput, copyBtn, copyIcon, copyText)
    }
  } catch (err) {
    console.error("Copy failed:", err)
    showNotification("Failed to copy link. Please copy manually.", "error")
  }
}

function fallbackCopy(linkInput, copyBtn, copyIcon, copyText) {
  try {
    document.execCommand("copy")
    showCopySuccess(copyBtn, copyIcon, copyText)
  } catch (err) {
    console.error("Fallback copy failed:", err)
    showNotification("Failed to copy link. Please copy manually.", "error")
  }
}

function showCopySuccess(copyBtn, copyIcon, copyText) {
  // Update button appearance
  copyBtn.classList.add("copied")
  copyIcon.className = "fas fa-check"
  copyText.textContent = "Copied!"

  // Reset button after 2 seconds
  setTimeout(() => {
    copyBtn.classList.remove("copied")
    copyIcon.className = "fas fa-copy"
    copyText.textContent = "Copy Link"
  }, 2000)

  // Show success message
  showNotification("Forms link copied to clipboard!", "success")
}

// Show notification function
function showNotification(message, type) {
  const notification = document.createElement("div")
  notification.className = `alert alert-${type === "success" ? "success" : "error"}`
  notification.innerHTML = `
    <i class="fas fa-${type === "success" ? "check-circle" : "exclamation-circle"}"></i>
    ${message}
  `
  notification.style.opacity = "0"
  notification.style.transform = "translateY(-10px)"

  // Insert at the top of the settings container
  const container = document.querySelector(".settings-container")
  container.insertBefore(notification, container.firstChild)

  // Animate in
  setTimeout(() => {
    notification.style.opacity = "1"
    notification.style.transform = "translateY(0)"
  }, 10)

  // Auto remove after 3 seconds
  setTimeout(() => {
    notification.style.opacity = "0"
    notification.style.transform = "translateY(-10px)"
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove()
      }
    }, 300)
  }, 3000)
}

function handleEmailChange() {
  const email = emailInput.value.trim()

  clearTimeout(emailCheckTimeout)
  resetEmailStatus()

  if (email === originalValues.email) {
    // Same as original email, mark as available
    isEmailAvailable = true
    checkFormChanges()
    return
  }

  if (!isValidEmail(email)) {
    showEmailStatus("invalid", "Please enter a valid email address")
    isEmailAvailable = false
    checkFormChanges()
    return
  }

  showEmailStatus("checking", "Checking availability...")

  emailCheckTimeout = setTimeout(() => {
    checkEmailAvailability(email)
  }, 500)
}

function handlePhoneChange() {
  const phone = phoneInput.value.trim()

  // Remove all non-digits
  let value = phone.replace(/\D/g, "")

  // Limit to 11 digits
  if (value.length > 11) {
    value = value.slice(0, 11)
  }

  // Update the input value
  phoneInput.value = value

  // Validate phone number format (11 digits)
  if (value.length > 0 && !/^[0-9]{11}$/.test(value)) {
    phoneInput.className = "form-control invalid"
  } else {
    phoneInput.className = "form-control"
  }

  checkFormChanges()
}

function handleCompanyChange() {
  const company = companyInput.value.trim()

  clearTimeout(companyCheckTimeout)
  resetCompanyStatus()

  if (company === originalValues.company) {
    // Same as original company, mark as available
    isCompanyAvailable = true
    checkFormChanges()
    return
  }

  if (company.length === 0) {
    // Empty company is allowed
    isCompanyAvailable = true
    checkFormChanges()
    return
  }

  if (company.length < 3) {
    showCompanyStatus("invalid", "Company name must be at least 3 characters")
    isCompanyAvailable = false
    checkFormChanges()
    return
  }

  showCompanyStatus("checking", "Checking availability...")

  companyCheckTimeout = setTimeout(() => {
    checkCompanyAvailability(company)
  }, 500)
}

function checkFormChanges() {
  const currentValues = {
    name: nameInput.value.trim(),
    email: emailInput.value.trim(),
    phone: phoneInput.value.trim(),
    company: companyInput.value.trim(),
  }

  // Check if any field has changed
  const hasChanges = Object.keys(originalValues).some((key) => currentValues[key] !== originalValues[key])

  // Check if all validations pass
  const isValid =
    isEmailAvailable &&
    isCompanyAvailable &&
    currentValues.name.length > 0 &&
    isValidEmail(currentValues.email) &&
    (currentValues.phone.length === 0 || /^[0-9]{11}$/.test(currentValues.phone))

  updateBtn.disabled = !hasChanges || !isValid
}

function resetEmailStatus() {
  emailInput.className = "form-control"
  emailStatusIcon.className = "status-icon"
  emailStatusMessage.textContent = ""
  emailStatusMessage.className = "status-message"
  isEmailAvailable = false
}

function showEmailStatus(status, message) {
  emailInput.className = `form-control ${status}`
  emailStatusIcon.className = `status-icon ${status}`
  emailStatusMessage.textContent = message
  emailStatusMessage.className = `status-message ${status}`

  if (status === "checking") {
    emailStatusIcon.innerHTML = '<i class="fas fa-spinner"></i>'
  } else if (status === "available" || status === "valid") {
    emailStatusIcon.innerHTML = '<i class="fas fa-check"></i>'
    isEmailAvailable = true
  } else {
    emailStatusIcon.innerHTML = '<i class="fas fa-times"></i>'
    isEmailAvailable = false
  }

  checkFormChanges()
}

function resetCompanyStatus() {
  companyInput.className = "form-control"
  companyStatusIcon.className = "status-icon"
  companyStatusMessage.textContent = ""
  companyStatusMessage.className = "status-message"
  isCompanyAvailable = false
}

function showCompanyStatus(status, message) {
  companyInput.className = `form-control ${status}`
  companyStatusIcon.className = `status-icon ${status}`
  companyStatusMessage.textContent = message
  companyStatusMessage.className = `status-message ${status}`

  if (status === "checking") {
    companyStatusIcon.innerHTML = '<i class="fas fa-spinner"></i>'
  } else if (status === "available" || status === "valid") {
    companyStatusIcon.innerHTML = '<i class="fas fa-check"></i>'
    isCompanyAvailable = true
  } else {
    companyStatusIcon.innerHTML = '<i class="fas fa-times"></i>'
    isCompanyAvailable = false
  }

  checkFormChanges()
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function checkEmailAvailability(email) {
  fetch("../Query/check_email.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email: email }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.available) {
        showEmailStatus("available", "Email is available")
      } else {
        showEmailStatus("unavailable", "Email is already taken")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showEmailStatus("unavailable", "Error checking availability")
    })
}

function checkCompanyAvailability(company) {
  fetch("../Query/check_company_profile.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ company_name: company }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.available) {
        showCompanyStatus("available", "Company name is available")
      } else {
        showCompanyStatus("unavailable", "Company name is already taken")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showCompanyStatus("unavailable", "Error checking availability")
    })
}

// Password validation
const currentPasswordInput = document.getElementById("currentPassword")
const newPasswordInput = document.getElementById("newPassword")
const confirmPasswordInput = document.getElementById("confirmPassword")
const currentPasswordIcon = document.getElementById("currentPasswordIcon")
const currentPasswordMessage = document.getElementById("currentPasswordMessage")
const confirmPasswordIcon = document.getElementById("confirmPasswordIcon")
const confirmPasswordMessage = document.getElementById("confirmPasswordMessage")
const changePasswordBtn = document.getElementById("changePasswordBtn")

let currentPasswordTimeout
let isCurrentPasswordValid = false

currentPasswordInput.addEventListener("input", function () {
  const password = this.value.trim()

  clearTimeout(currentPasswordTimeout)
  resetCurrentPasswordStatus()

  if (password.length === 0) {
    isCurrentPasswordValid = false
    updatePasswordButton()
    return
  }

  showCurrentPasswordStatus("checking", "Verifying password...")

  currentPasswordTimeout = setTimeout(() => {
    checkCurrentPassword(password)
  }, 500)
})

confirmPasswordInput.addEventListener("input", () => {
  validatePasswordMatch()
})

newPasswordInput.addEventListener("input", () => {
  validatePasswordMatch()
})

function resetCurrentPasswordStatus() {
  currentPasswordInput.className = "form-control"
  currentPasswordIcon.className = "status-icon"
  currentPasswordMessage.textContent = ""
  currentPasswordMessage.className = "status-message"
  isCurrentPasswordValid = false
  updatePasswordButton()
}

function showCurrentPasswordStatus(status, message) {
  currentPasswordInput.className = `form-control ${status}`
  currentPasswordIcon.className = `status-icon ${status}`
  currentPasswordMessage.textContent = message
  currentPasswordMessage.className = `status-message ${status}`

  if (status === "checking") {
    currentPasswordIcon.innerHTML = '<i class="fas fa-spinner"></i>'
  } else if (status === "valid") {
    currentPasswordIcon.innerHTML = '<i class="fas fa-check"></i>'
    isCurrentPasswordValid = true
  } else {
    currentPasswordIcon.innerHTML = '<i class="fas fa-times"></i>'
    isCurrentPasswordValid = false
  }

  updatePasswordButton()
}

function validatePasswordMatch() {
  const newPassword = newPasswordInput.value
  const confirmPassword = confirmPasswordInput.value

  if (confirmPassword.length === 0) {
    confirmPasswordIcon.className = "status-icon"
    confirmPasswordMessage.textContent = ""
    confirmPasswordMessage.className = "status-message"
    confirmPasswordInput.className = "form-control"
    updatePasswordButton()
    return
  }

  if (newPassword === confirmPassword) {
    confirmPasswordInput.className = "form-control valid"
    confirmPasswordIcon.className = "status-icon valid"
    confirmPasswordIcon.innerHTML = '<i class="fas fa-check"></i>'
    confirmPasswordMessage.textContent = "Passwords match"
    confirmPasswordMessage.className = "status-message valid"
  } else {
    confirmPasswordInput.className = "form-control invalid"
    confirmPasswordIcon.className = "status-icon invalid"
    confirmPasswordIcon.innerHTML = '<i class="fas fa-times"></i>'
    confirmPasswordMessage.textContent = "Passwords do not match"
    confirmPasswordMessage.className = "status-message invalid"
  }

  updatePasswordButton()
}

function updatePasswordButton() {
  const newPassword = newPasswordInput.value
  const confirmPassword = confirmPasswordInput.value
  const passwordsMatch = newPassword === confirmPassword && newPassword.length >= 8

  changePasswordBtn.disabled = !(isCurrentPasswordValid && passwordsMatch)
}

function checkCurrentPassword(password) {
  fetch("../Query/verify_password.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      password: password,
      user_id: document.querySelector('input[name="user_id"]').value,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.valid) {
        showCurrentPasswordStatus("valid", "Password is correct")
      } else {
        showCurrentPasswordStatus("invalid", "Incorrect password")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showCurrentPasswordStatus("invalid", "Error verifying password")
    })
}

// Form submission validation
document.getElementById("passwordForm").addEventListener("submit", (e) => {
  const newPassword = newPasswordInput.value
  const confirmPassword = confirmPasswordInput.value

  if (newPassword !== confirmPassword) {
    e.preventDefault()
    alert("New password and confirm password do not match.")
    return
  }

  if (newPassword.length < 8) {
    e.preventDefault()
    alert("Password must be at least 8 characters long.")
    return
  }

  if (!isCurrentPasswordValid) {
    e.preventDefault()
    alert("Please enter your correct current password.")
    return
  }
})

// Reset button functionality
document.getElementById("resetBtn").addEventListener("click", () => {
  // Reset all fields to original values
  nameInput.value = originalValues.name
  emailInput.value = originalValues.email
  phoneInput.value = originalValues.phone
  companyInput.value = originalValues.company

  // Reset all statuses
  resetEmailStatus()
  resetCompanyStatus()

  // Mark as available since we're back to original values
  isEmailAvailable = true
  isCompanyAvailable = true

  // Reset input classes
  nameInput.className = "form-control"
  phoneInput.className = "form-control"

  checkFormChanges()
})

// Initialize form state
document.addEventListener("DOMContentLoaded", () => {
  // Set initial availability states
  isEmailAvailable = true
  isCompanyAvailable = true
  checkFormChanges()
})
