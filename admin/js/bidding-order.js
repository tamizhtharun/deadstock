document.addEventListener("DOMContentLoaded", () => {
  const fromDate = document.getElementById("fromDate")
  const toDate = document.getElementById("toDate")
  const clearDatesBtn = document.getElementById("clearDates")
  const statusFilter = document.getElementById("statusFilter")
  const biddingOrderTableContainer = document.getElementById("bidding-order-table-container")
  const noBidsMessage = document.getElementById("no-bids-message")

  const today = new Date().toISOString().split("T")[0]
  fromDate.max = today
  toDate.max = today

  function filterRows() {
    const rows = document.querySelectorAll(".bid-order-row")
    let hasVisibleRows = false

    const selectedStatus = statusFilter.value
    const fDate = fromDate.value ? new Date(fromDate.value) : null
    const tDate = toDate.value ? new Date(toDate.value) : null

    rows.forEach((row) => {
      const rowDate = new Date(row.getAttribute("data-date"))
      const rowStatus = row.getAttribute("data-status")

      let showRow = true

      // Status filter
      if (selectedStatus && rowStatus !== selectedStatus) {
        showRow = false
      }

      // Date filter
      if (showRow) {
        if (fDate && !tDate) {
          // Single date filter
          showRow = rowDate.toISOString().split("T")[0] === fDate.toISOString().split("T")[0]
        } else if (fDate && tDate) {
          // Date range filter
          showRow = rowDate >= fDate && rowDate <= tDate
        } else if (!fDate && tDate) {
          // Single end date filter
          showRow = rowDate <= tDate
        }
      }

      row.style.display = showRow ? "" : "none"
      if (showRow) hasVisibleRows = true
    })

    biddingOrderTableContainer.style.display = hasVisibleRows ? "block" : "none"
    noBidsMessage.style.display = hasVisibleRows ? "none" : "block"
  }

  function showAllOrders() {
    document.querySelectorAll(".bid-order-row").forEach((row) => {
      row.style.display = ""
    })
    biddingOrderTableContainer.style.display = "block"
    noBidsMessage.style.display = "none"
  }

  fromDate.addEventListener("change", filterRows)
  toDate.addEventListener("change", filterRows)
  statusFilter.addEventListener("change", filterRows)

  clearDatesBtn.addEventListener("click", () => {
    fromDate.value = ""
    toDate.value = ""
    fromDate.max = today
    toDate.min = ""
    if (statusFilter.value) {
      filterRows()
    } else {
      showAllOrders()
    }
  })
})

function updateOrderStatus(orderId, newStatus) {
  if (newStatus === "shipped") {
    const trackingId = prompt("Please enter the tracking ID:")
    console.log(trackingId);
    if (!trackingId) {
      alert("Tracking ID is required for shipped status.")
      return
    }
    if (!confirm(`Are you sure you want to update the order status to ${newStatus} with tracking ID ${trackingId}?`)) {
      return
    }
    updateOrderStatusWithTracking(orderId, newStatus, trackingId)
  } else {
    if (!confirm(`Are you sure you want to update the order status to ${newStatus}?`)) {
      return
    }
    updateOrderStatusWithTracking(orderId, newStatus)
  }
}
function updateOrderStatusWithTracking(orderId, newStatus, trackingId = null) {
  let url = `process_bid_order.php?action=update_status&order_id=${orderId}&status=${newStatus}`
  if (trackingId) {
    url += `&tracking_id=${encodeURIComponent(trackingId)}`
  }

  fetch(url)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      if (data.success) {
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`)
        const statusCell = row.querySelector(".order-status")
        const actionCell = row.querySelector(".action-column")
        const processingTimeCell = row.querySelector(".processing-time")
        const trackingIdCell = row.querySelector(".tracking-id")

        statusCell.innerHTML = `<span class="status-badge status-${newStatus}">${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}</span>`
        row.setAttribute("data-status", newStatus)

        if (newStatus === "processing") {
          processingTimeCell.textContent = data.processing_time
          updateActionButtons(actionCell, orderId, newStatus)
        } else if (newStatus === "shipped") {
          trackingIdCell.textContent = data.tracking_id
          updateActionButtons(actionCell, orderId, newStatus)
        } else {
          actionCell.innerHTML = `
            <button class="btn-status-update disabled">
              <i class="fa fa-lock"></i>
              No Actions Available
            </button>
          `
        }

        alert(data.message)
      } else {
        throw new Error(data.message || "Failed to update order status")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("Failed to update order status: " + error.message)
    })
}

function updateActionButtons(actionCell, orderId, status) {
  if (status === "delivered" || status === "canceled") {
    actionCell.innerHTML = `
          <button class="btn-status-update disabled">
              <i class="fa fa-lock"></i> No Actions Available
          </button>
      `
  } else {
    let nextStatuses = []
    switch (status) {
      case "pending":
        nextStatuses = ["processing", "canceled"]
        break
      case "processing":
        nextStatuses = ["shipped", "canceled"]
        break
      case "shipped":
        nextStatuses = ["delivered", "canceled"]
        break
    }

    let buttonsHtml = ""
    nextStatuses.forEach((nextStatus) => {
      let icon = ""
      switch (nextStatus) {
        case "processing":
          icon = "fa-cog"
          break
        case "shipped":
          icon = "fa-truck"
          break
        case "delivered":
          icon = "fa-check-circle"
          break
        case "canceled":
          icon = "fa-times-circle"
          break
      }
      buttonsHtml += `
              <button 
                  class="btn-status-update" 
                  onclick="updateOrderStatus(${orderId}, '${nextStatus}')"
              >
                  <i class="fa ${icon}"></i>
                  ${nextStatus.charAt(0).toUpperCase() + nextStatus.slice(1)}
              </button>
          `
    })

    actionCell.innerHTML = `<div class="action-buttons">${buttonsHtml}</div>`
  }
}

function sendOrder(button) {
  if (!confirm("Are you sure you want to send this order to seller?")) {
    return
  }

  const data = button.dataset
  const url = `process_bid_order.php?action=send&bid_id=${data.bidId}&product_id=${data.productId}&user_id=${data.userId}&seller_id=${data.sellerId}&quantity=${data.quantity}&price=${data.price}`

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const row = button.closest("tr")
        const statusCell = row.querySelector(".order-status")
        const actionCell = row.querySelector(".action-column")

        statusCell.innerHTML = '<span class="status-badge status-pending">Pending</span>'
        row.setAttribute("data-status", "pending")
        row.setAttribute("data-order-id", data.order_id)

        // Uncomment this section if needed to dynamically update action buttons
        /*
          actionCell.innerHTML = `
            <div class="action-buttons">
                <button 
                    class="btn-status-update" 
                    onclick="updateOrderStatus(${data.order_id}, 'processing')"
                >
                    <i class="fa fa-cog"></i>
                    Processing
                </button>
                <button 
                    class="btn-status-update" 
                    onclick="updateOrderStatus(${data.order_id}, 'canceled')"
                >
                    <i class="fa fa-times-circle"></i>
                    Canceled
                </button>
            </div>
          `;
          */

        alert("Order sent to seller successfully.")
        location.reload() // Reload the page
      } else {
        alert("Failed to send order: " + data.message)
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("Failed to send order. Please try again.")
    })
}

function sendAllOrders() {
  if (!confirm("Are you sure you want to send all orders to sellers?")) {
    return
  }

  fetch("process_bid_order.php?action=sendall")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(`Successfully sent ${data.total_orders} orders to sellers.`)
        location.reload() // Reload the page to reflect changes
      } else {
        alert("Failed to send all orders: " + data.message)
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("Failed to send all orders. Please try again.")
    })
}

function openImageModal(imgSrc) {
  const modal = document.getElementById("imageModal")
  const modalImg = document.getElementById("modalImage")

  modal.classList.add("show")
  modalImg.src = imgSrc
  document.body.style.overflow = "hidden"
}

function closeImageModal() {
  const modal = document.getElementById("imageModal")
  const modalImg = document.getElementById("modalImage")

  modal.classList.remove("show")
  setTimeout(() => {
    modalImg.src = ""
  }, 300)
  document.body.style.overflow = "auto"
}

// Event listeners for modal closing
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("imageModal")
  const closeBtn = document.querySelector(".close-modal")

  // Close on clicking outside the image
  modal.addEventListener("click", (e) => {
    if (e.target === modal || e.target.classList.contains("modal-container")) {
      closeImageModal()
    }
  })

  // Close on clicking close button
  closeBtn.addEventListener("click", (e) => {
    e.stopPropagation()
    closeImageModal()
  })

  // Close on ESC key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeImageModal()
    }
  })

  // Prevent image click from closing modal
  document.getElementById("modalImage").addEventListener("click", (e) => {
    e.stopPropagation()
  })
})

//// Modal functionality
const sellerModal = document.getElementById("sellerModal")
const closeSellerModal = document.getElementById("closeSellerModal")
const closeSellerX = document.querySelector(".seller-close")

function openSellerModal(sellerId) {
  console.log("Opening modal for seller ID:", sellerId)
  sellerModal.style.display = "block"
  document.body.style.overflow = "hidden"
  fetchSellerData(sellerId)
}

const closeSellerModalFn = () => {
  sellerModal.style.display = "none"
  document.body.style.overflow = "auto"
}

closeSellerModal.onclick = closeSellerModalFn
closeSellerX.onclick = closeSellerModalFn

// Close if clicked outside
window.onclick = (event) => {
  if (event.target === sellerModal) {
    closeSellerModalFn()
  }
}

// Tab functionality
const tabButtons = document.querySelectorAll(".seller-tab-button")
const tabPanes = document.querySelectorAll(".seller-tab-pane")

tabButtons.forEach((button) => {
  button.addEventListener("click", () => {
    tabButtons.forEach((btn) => btn.classList.remove("active"))
    tabPanes.forEach((pane) => pane.classList.remove("active"))
    button.classList.add("active")
    document.getElementById(button.dataset.tab).classList.add("active")
  })
})

function fetchSellerData(sellerId) {
  console.log("Fetching data for seller ID:", sellerId)
  fetch(`get_seller_data.php?seller_id=${sellerId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      console.log("Received data:", data)
      if (data.error) {
        throw new Error(data.error)
      } else {
        updateSellerModal(data)
        updateCharts(data)
        updateCertificationTab(data.certifications)
      }
    })
    .catch((error) => {
      console.error("Fetch error:", error)
      alert("Failed to fetch seller data. Please try again. Error: " + error.message)
    })
}

function updateSellerModal(data) {
  const seller = data.seller
  const defaultImage = "https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png"

  document.querySelector("#profile .seller-info-grid").innerHTML = `
        <div class="seller-info-item"><label>Name</label><span>${seller.seller_name}</span></div>
        <div class="seller-info-item">
            <label>Photo</label>
            <div class="seller-photo-container">
                <img src="${seller.seller_photo || defaultImage}" alt="Seller Photo" class="seller-photo">
            </div>
            <div class="photo-modal">
                <img src="${seller.seller_photo || defaultImage}" alt="Seller Photo">
            </div>
        </div>
        <div class="seller-info-item"><label>Seller ID</label><span>${seller.unique_seller_id}</span></div>       
        <div class="seller-info-item">
    <label>Total Revenue</label>
    <span>${seller.formatted_revenue}</span>
</div>

        <div class="seller-info-item"><label>Email</label><span>${seller.seller_email}</span></div>
        <div class="seller-info-item"><label>Company Name</label><span>${seller.seller_cname}</span></div>
        <div class="seller-info-item"><label>GST Number</label><span>${seller.seller_gst}</span></div>
        <div class="seller-info-item"><label>Registration Date</label><span>${seller.created_at}</span></div>
        <div class="seller-info-item"><label>Status</label><span class="status-badge ${seller.seller_status ? "active" : "inactive"}">${seller.seller_status ? "Active" : "Inactive"}</span></div>
        <div class="seller-info-item seller-address">
            <label>Business Address</label>
            <span>${seller.seller_address} <br> ${seller.seller_city}, ${seller.seller_state} ${seller.seller_zipcode}</span>
        </div>
        <div class="seller-info-item"><label>Phone</label><span>${seller.seller_phone}</span></div>
    `

  // Add click event listeners for the photo modal
  const photoContainer = document.querySelector(".seller-photo-container")
  const modal = document.querySelector(".photo-modal")
  const photo = photoContainer.querySelector(".seller-photo")

  photo.addEventListener("click", () => {
    modal.classList.add("active")
  })

  modal.addEventListener("click", () => {
    modal.classList.remove("active")
  })

  document.querySelector("#products .seller-stats-grid").innerHTML = `
        <div class="seller-stat-card"><h3>Total Products</h3><p>${data.products.total}</p></div>
        <div class="seller-stat-card"><h3>Active Products</h3><p>${data.products.active}</p></div>
        <div class="seller-stat-card"><h3>Categories</h3><p>${data.products.categories}</p></div>
    `

  document.querySelector("#bidding .seller-stats-grid").innerHTML = `
        <div class="seller-stat-card"><h3>Total Bids</h3><p>${data.bidding.total}</p></div>
        <div class="seller-stat-card"><h3>Winning Bids</h3><p>${data.bidding.winning}</p></div>
        <div class="seller-stat-card"><h3>Avg. Bid Amount</h3><p>₹${data.bidding.avg_amount.toFixed(2)}</p></div>
    `

  document.querySelector("#orders .seller-stats-grid").innerHTML = `
        <div class="seller-stat-card"><h3>Total Orders</h3><p>${data.orders.total}</p></div>
        <div class="seller-stat-card"><h3>Pending Orders</h3><p>${data.orders.pending}</p></div>
        <div class="seller-stat-card"><h3>Success Rate</h3><p>${data.orders.success_rate.toFixed(2)}%</p></div>
    `
}

function updateCharts(data) {
  ;["productsChart", "biddingChart", "ordersChart", "orderStatusChart"].forEach((chartId) => {
    const chartInstance = Chart.getChart(chartId)
    if (chartInstance) {
      chartInstance.destroy()
    }
  })

  updateProductsChart(data.products.chart_data)
  updateBiddingChart(data.bidding.chart_data)
  updateOrdersChart(data.orders.chart_data)
  updateOrderStatusChart(data.orders.status_data)
}

function updateProductsChart(data) {
  // Extract last 10 days, ensuring professional date formatting
  const limitedData = {
    labels: data.labels.slice(-10).map((date) => {
      const options = { month: "short", day: "numeric" }
      return new Date(date).toLocaleDateString("en-US", options)
    }),
    values: data.values.slice(-10),
  }

  const ctx = document.getElementById("productsChart").getContext("2d")
  new Chart(ctx, {
    type: "line",
    data: {
      labels: limitedData.labels,
      datasets: [
        {
          label: "Active Products",
          data: limitedData.values,
          borderColor: "rgba(37, 99, 235, 1)",
          backgroundColor: "rgba(37, 99, 235, 0.1)",
          fill: true,
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          text: "Products Added in Last 10 Days",
          padding: {
            bottom: 20,
          },
        },
        tooltip: {
          callbacks: {
            title: (context) => context[0].label,
            label: (context) => `Products: ${context.parsed.y}`,
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { display: false },
        },
        x: {
          grid: { display: false },
        },
      },
    },
  })
}
function updateBiddingChart(data) {
  const ctx = document.getElementById("biddingChart").getContext("2d")
  new Chart(ctx, {
    type: "line",
    data: {
      labels: data.labels,
      datasets: [
        {
          label: "Total Bids",
          data: data.values,
          borderColor: "rgba(124, 58, 237, 1)",
          backgroundColor: "rgba(124, 58, 237, 0.1)",
          fill: true,
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
        title: {
          display: true,
          text: "Bidding Activity (Last 7 Days)",
          padding: {
            bottom: 16,
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            display: false,
          },
        },
        x: {
          grid: {
            display: false,
          },
        },
      },
    },
  })
}

function updateOrdersChart(data) {
  const ctx = document.getElementById("ordersChart").getContext("2d")
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.labels,
      datasets: [
        {
          label: "Orders",
          data: data.values,
          backgroundColor: "rgba(8, 145, 178, 0.8)",
          borderRadius: 4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
        title: {
          display: true,
          text: "Daily Orders",
          padding: {
            bottom: 16,
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            display: false,
          },
        },
        x: {
          grid: {
            display: false,
          },
        },
      },
    },
  })
}

function updateOrderStatusChart(data) {
  const ctx = document.getElementById("orderStatusChart").getContext("2d")
  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: data.labels,
      datasets: [
        {
          data: data.values,
          backgroundColor: [
            "rgba(234, 179, 8, 0.8)",
            "rgba(8, 145, 178, 0.8)",
            "rgba(22, 163, 74, 0.8)",
            "rgba(220, 38, 38, 0.8)",
          ],
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            padding: 20,
            usePointStyle: true,
            pointStyle: "circle",
          },
        },
        title: {
          display: true,
          text: "Order Status Distribution",
          padding: {
            bottom: 16,
          },
        },
      },
      cutout: "70%",
    },
  })
}

function updateCertificationTab(certifications) {
  const certificationGrid = document.getElementById("certificationGrid")
  certificationGrid.innerHTML = ""

  console.log("Updating certification tab with data:", certifications)

  if (!certifications || certifications.length === 0) {
    const noCertMessage = document.createElement("div")
    noCertMessage.className = "no-certification-message"
    noCertMessage.innerHTML = `
      <i class="fas fa-certificate"></i>
      <h3>No Certifications Available</h3>
      <p>This seller has not uploaded any brand certifications yet.</p>
    `
    certificationGrid.appendChild(noCertMessage)
    return
  }

  certifications.forEach((cert) => {
    const certCard = document.createElement("div")
    certCard.className = "certification-card"
    certCard.innerHTML = `
      <div class="certification-header">
        <img src="../assets/uploads/brand-logos/${cert.brand_logo}" alt="${cert.brand_name} logo" class="brand-logo">
        <h3>${cert.brand_name}</h3>
      </div>
      <div class="certification-body">
        <p class="cert-description">${cert.brand_description ? cert.brand_description.substring(0, 100) + "..." : "No description available."}</p>
        <p><strong>Valid Until:</strong> ${cert.valid_to ? new Date(cert.valid_to).toLocaleDateString() : "Not specified"}</p>
        <p><strong>Issued On:</strong> ${cert.created_at ? new Date(cert.created_at).toLocaleDateString() : "Not specified"}</p>
      </div>
      <div class="certification-footer">
        <button class="view-certificate-btn" data-certificate="../assets/uploads/certificates/${cert.brand_certificate}">View Certificate</button>
      </div>
    `
    certificationGrid.appendChild(certCard)
  })

  // Add event listeners to view certificate buttons
 document.querySelectorAll(".view-certificate-btn").forEach((button) => {
  button.addEventListener("click", function () {
    const certificateUrl = this.getAttribute("data-certificate")
    // Check if certificate exists before opening
    fetch(certificateUrl, { method: "HEAD" })
      .then((response) => {
        if (response.ok) {
          window.open(certificateUrl, "_blank")
        } else {
          window.open("./error-pages/certificate-not-found.html", "_blank")
        }
      })
      .catch(() => {
        window.open("./error-pages/certificate-not-found.html", "_blank")
      })
  })
})
}
