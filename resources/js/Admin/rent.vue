<template>
  <div class="rent-page">
    <main class="main-content">
      <div class="rent-toolbar">
        <div>
          <h2>Rental requests</h2>
          <p>Review booking requests, discount claims, and pickup or return scans.</p>
        </div>
        <div class="scan-actions">
          <button class="scan-button" type="button" @click="openScanner('pickup')">
            Scan pickup QR
          </button>
          <button class="scan-button return-scan-button" type="button" @click="openScanner('return')">
            Scan return QR
          </button>
        </div>
      </div>

      <section class="content-section">
        <table id="rentalTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Vehicle</th>
              <th>Rental Dates</th>
              <th>Availability</th>
              <th>Payment Receipt</th>
              <th>Discount</th>
              <th>QR stage</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="rental in rentals" :key="rental.id">
              <td>{{ rental.id }}</td>
              <td>{{ rental.customer }}</td>
              <td>{{ rental.vehicle }}</td>

              <td>
                {{ formatDate(rental.rental_start) }} – {{ formatDate(rental.rental_end) }}
                <small class="rental-duration">{{ rental.days }} {{ rental.days === 1 ? 'day' : 'days' }} · ₱{{ formatMoney(rental.total_amount) }}</small>
              </td>

              <td class="availability-cell">
                <span v-if="rental.maintenance_conflict" class="conflict-badge">Maintenance conflict</span>
                <button
                  v-if="rental.maintenance_conflict && ['pending', 'approved'].includes(rental.status) && !rental.qr_verified_at && !rental.returned_at"
                  class="resolve-button"
                  type="button"
                  @click="openMaintenanceResolver(rental)"
                >Resolve</button>
                <span v-else-if="!rental.maintenance_conflict" class="muted">Clear</span>
              </td>

              <td>
                <span
                  v-if="rental.proof_file"
                  class="receipt-link"
                  @click="openReceipt(rental)"
                >
                  {{ rental.proof_file }}
                </span>
                <span v-else>N/A</span>
              </td>

              <td>
                <template v-if="rental.discount_type">
                  <span class="discount-kind">{{ discountLabel(rental.discount_type) }}</span>
                  <span class="status-badge" :class="`discount-${rental.discount_status}`">
                    {{ rental.discount_status }}
                  </span>
                  <small v-if="rental.discount_status === 'verified'" class="discount-amount">
                    −₱{{ formatMoney(rental.discount_amount) }} / day
                  </small>
                </template>
                <span v-else class="muted">None</span>
              </td>

              <td>
                <span v-if="rental.returned_at" class="pickup-verified">
                  Returned<br />{{ formatDate(rental.returned_at) }}
                </span>
                <span v-else-if="rental.qr_verified_at" class="pickup-verified">
                  Pickup verified<br />{{ formatDate(rental.qr_verified_at) }}
                </span>
                <span v-else class="muted">Not scanned</span>
              </td>

              <td class="status-cell">
                <span class="status-badge" :class="`status-${rental.status}`">
                  {{ rental.status }}
                </span>
              </td>

              <td>
                <template v-if="rental.status === 'pending'">
                  <button class="approveBtn button-primary" @click="approveRental(rental.id)">
                    Approve
                  </button>

                  <button class="denyBtn button-secondary" @click="denyRental(rental.id)">
                    Deny
                  </button>
                </template>

                <template v-else>
                  <span class="processed-text">Processed</span>
                </template>

                <div
                  v-if="rental.status === 'approved' && rental.discount_status === 'pending'"
                  class="discount-actions"
                >
                  <button
                    class="verify-discount-btn"
                    :disabled="!rental.qr_verified_at"
                    :title="rental.qr_verified_at ? 'Confirm the original ID was checked' : 'Scan this booking QR at pickup first'"
                    @click="decideDiscount(rental, 'verified')"
                  >
                    Verify ID
                  </button>
                  <button
                    class="reject-discount-btn"
                    :disabled="!rental.qr_verified_at"
                    :title="rental.qr_verified_at ? 'Reject this discount request' : 'Scan this booking QR at pickup first'"
                    @click="decideDiscount(rental, 'rejected')"
                  >
                    Reject
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!rentals.length">
              <td colspan="10" class="empty-state">
                No rentals found.
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>

    <!-- Receipt Modal -->
    <div v-if="showReceiptModal" id="receiptModal" @click.self="closeReceipt">
      <span class="closeReceipt" @click="closeReceipt">&times;</span>

      <div class="receipt-placeholder">
        <h3>Receipt Preview</h3>

        <p>{{ selectedReceipt }}</p>

        <img
          v-if="selectedReceiptUrl && isImage(selectedReceiptUrl)"
          :src="selectedReceiptUrl"
          class="receipt-image"
        />

        <div v-else-if="selectedReceiptUrl" class="receipt-file-box">
          <a :href="selectedReceiptUrl" target="_blank">
            Open Receipt File
          </a>
        </div>

        <p v-else>No file uploaded yet.</p>
      </div>
    </div>

    <div v-if="showMaintenanceResolver" class="resolver-overlay" @click.self="closeMaintenanceResolver">
      <section class="resolver-modal" role="dialog" aria-modal="true" aria-labelledby="maintenanceResolverTitle">
        <button class="resolver-close" type="button" aria-label="Close" @click="closeMaintenanceResolver">&times;</button>
        <h2 id="maintenanceResolverTitle">Resolve maintenance conflict</h2>
        <p v-if="maintenanceRental" class="resolver-summary">
          {{ maintenanceRental.customer }} · {{ maintenanceRental.vehicle }} · {{ formatDate(maintenanceRental.rental_start) }} – {{ formatDate(maintenanceRental.rental_end) }}
        </p>

        <label class="resolver-option">
          <input v-model="resolutionAction" type="radio" value="reschedule" />
          <span><strong>Reschedule this booking</strong><small>Keep this car and choose a clear pickup date and time.</small></span>
        </label>
        <div v-if="resolutionAction === 'reschedule'" class="resolver-field">
          <label for="rescheduleStart">New pickup date and time</label>
          <input id="rescheduleStart" v-model="resolutionDateTime" type="datetime-local" :min="minimumDateTime" />
        </div>

        <label class="resolver-option">
          <input v-model="resolutionAction" type="radio" value="reassign" />
          <span><strong>Assign another car</strong><small>Keep the pickup date and time.</small></span>
        </label>
        <div v-if="resolutionAction === 'reassign'" class="resolver-field">
          <label for="replacementVehicle">Available replacement car</label>
          <select id="replacementVehicle" v-model="replacementVehicleId" @focus="loadReplacementVehicles">
            <option value="">Choose a car</option>
            <option v-for="vehicle in replacementVehicles" :key="vehicle.id" :value="vehicle.id">
              {{ vehicle.reg_no }} · {{ vehicle.brand }} {{ vehicle.model }}
            </option>
          </select>
          <button class="resolver-refresh" type="button" @click="loadReplacementVehicles">Refresh available cars</button>
        </div>

        <label class="resolver-option">
          <input v-model="resolutionAction" type="radio" value="cancel" />
          <span><strong>Cancel this booking</strong><small>Use when the customer does not want either change.</small></span>
        </label>

        <label v-if="resolutionAction === 'cancel'" class="resolver-check">
          <input v-model="customerNotified" type="checkbox" /> Customer has been notified about the cancellation
        </label>
        <label v-else class="resolver-check">
          <input v-model="customerAcknowledged" type="checkbox" /> Customer agreed to this change
        </label>

        <p v-if="resolverError" class="resolver-error">{{ resolverError }}</p>
        <div class="resolver-actions">
          <button class="resolver-save" type="button" :disabled="savingResolution" @click="saveMaintenanceResolution">
            {{ savingResolution ? 'Saving…' : 'Save resolution' }}
          </button>
          <button class="resolver-cancel" type="button" @click="closeMaintenanceResolver">Close</button>
        </div>
      </section>
    </div>

    <div v-if="showScanner" class="scanner-overlay" @click.self="closeScanner">
      <section class="scanner-modal" role="dialog" aria-modal="true" aria-labelledby="scannerTitle">
        <button class="scanner-close" type="button" aria-label="Close scanner" @click="closeScanner">&times;</button>
        <h2 id="scannerTitle">{{ scanAction === 'pickup' ? 'Pickup verification' : 'Return verification' }}</h2>
        <p class="scanner-intro">Scan the QR shown in the customer's approved booking.</p>

        <div v-if="!scanResult" id="booking-qr-reader" class="qr-reader"></div>
        <p v-if="scannerError" class="scanner-error">{{ scannerError }}</p>
        <button
          v-if="scannerError && !scanResult"
          class="scanner-secondary"
          type="button"
          @click="startQrScanner"
        >
          Try camera again
        </button>

        <div v-if="scanResult" class="scan-result">
          <p class="scan-success">{{ scanMessage }}</p>
          <dl>
            <div><dt>Booking</dt><dd>R-{{ String(scanResult.id).padStart(5, '0') }}</dd></div>
            <div><dt>Customer</dt><dd>{{ scanResult.customer }}</dd></div>
            <div><dt>Vehicle</dt><dd>{{ scanResult.vehicle }}</dd></div>
            <div v-if="scanResult.discount_type">
              <dt>Discount claim</dt>
              <dd>{{ discountLabel(scanResult.discount_type) }} — {{ scanResult.discount_status }}</dd>
            </div>
          </dl>
          <p v-if="scanAction === 'pickup' && scanResult.qr_verified_at" class="scan-stage-note">
            Pickup was recorded at {{ formatDate(scanResult.qr_verified_at) }}.
          </p>
          <p v-if="scanAction === 'return' && scanResult.returned_at" class="scan-stage-note">
            Return was recorded at {{ formatDate(scanResult.returned_at) }}.
          </p>
          <p v-if="scanResult.discount_status === 'pending'" class="id-reminder">
            Check the customer’s valid ID, then confirm or reject the discount in the booking row.
          </p>
        </div>

        <button class="scanner-secondary" type="button" @click="closeScanner">Close</button>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from "vue"
import api from "@/api"

const rentals = ref([])
const showReceiptModal = ref(false)
const selectedReceipt = ref("")
const selectedReceiptUrl = ref("")
const showScanner = ref(false)
const scannerError = ref("")
const scanMessage = ref("")
const scanResult = ref(null)
const scanAction = ref("pickup")
const isProcessingScan = ref(false)
const showMaintenanceResolver = ref(false)
const maintenanceRental = ref(null)
const resolutionAction = ref('reschedule')
const resolutionDateTime = ref('')
const replacementVehicles = ref([])
const replacementVehicleId = ref('')
const customerAcknowledged = ref(false)
const customerNotified = ref(false)
const savingResolution = ref(false)
const resolverError = ref('')
const minimumDateTime = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16)
let qrScanner = null
let scannerIsRunning = false
let rentalsPollTimer = null

/* ================= FETCH ================= */
const fetchRentals = async () => {
  try {
    const response = await api.get("/admin/rentals")

    if (response.data?.success) {
      rentals.value = response.data.rentals
    } else {
      rentals.value = []
    }

  } catch (error) {
    console.error("Failed to fetch rentals:", error)
    rentals.value = []
  }
}

const discountLabel = (type) => type === "senior" ? "Senior citizen" : "PWD"

const formatMoney = (amount) => Number(amount || 0).toLocaleString("en-PH", {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
})

const decideDiscount = async (rental, decision) => {
  const message = decision === "verified"
    ? "Confirm you checked the customer's valid ID and apply the configured discount to the daily rate?"
    : "Reject the discount request? The regular daily rate will apply."

  if (!window.confirm(message)) return

  try {
    const response = await api.put(`/admin/rentals/${rental.id}/discount`, { decision })
    if (response.data?.success) await fetchRentals()
  } catch (error) {
    window.alert(error.response?.data?.message || "Could not update the discount request.")
  }
}

const openMaintenanceResolver = (rental) => {
  maintenanceRental.value = rental
  resolutionAction.value = 'reschedule'
  resolutionDateTime.value = (rental.rental_start || '').slice(0, 16).replace(' ', 'T')
  replacementVehicles.value = []
  replacementVehicleId.value = ''
  customerAcknowledged.value = false
  customerNotified.value = false
  resolverError.value = ''
  showMaintenanceResolver.value = true
}

const closeMaintenanceResolver = () => {
  showMaintenanceResolver.value = false
  maintenanceRental.value = null
}

const loadReplacementVehicles = async () => {
  if (!maintenanceRental.value) return
  const date = (maintenanceRental.value.rental_start || '').slice(0, 10)
  const end_date = (maintenanceRental.value.rental_end || '').slice(0, 10) || date
  try {
    const response = await api.get('/admin/available-vehicles', {
      params: { date, end_date, exclude_vehicle_id: maintenanceRental.value.vehicle_id },
    })
    replacementVehicles.value = response.data.vehicles || []
  } catch (error) {
    resolverError.value = error.response?.data?.message || 'Could not load available cars.'
  }
}

const saveMaintenanceResolution = async () => {
  if (!maintenanceRental.value) return
  resolverError.value = ''
  savingResolution.value = true
  const payload = { action: resolutionAction.value }
  if (resolutionAction.value === 'reschedule') {
    if (!resolutionDateTime.value || !customerAcknowledged.value) {
      resolverError.value = 'Choose a new date and confirm the customer agreed.'
      savingResolution.value = false
      return
    }
    payload.rental_start = resolutionDateTime.value
    payload.customer_acknowledged = true
  } else if (resolutionAction.value === 'reassign') {
    if (!replacementVehicleId.value || !customerAcknowledged.value) {
      resolverError.value = 'Choose a replacement car and confirm the customer agreed.'
      savingResolution.value = false
      return
    }
    payload.vehicle_id = replacementVehicleId.value
    payload.customer_acknowledged = true
  } else {
    if (!customerNotified.value) {
      resolverError.value = 'Confirm the customer has been notified.'
      savingResolution.value = false
      return
    }
    payload.customer_notified = true
  }

  try {
    await api.post(`/admin/rentals/${maintenanceRental.value.id}/resolve-maintenance`, payload)
    closeMaintenanceResolver()
    await fetchRentals()
  } catch (error) {
    resolverError.value = error.response?.data?.message || Object.values(error.response?.data?.errors || {})[0]?.[0] || 'Could not resolve this booking.'
  } finally {
    savingResolution.value = false
  }
}

const openScanner = async (action = "pickup") => {
  scanAction.value = action
  scannerError.value = ""
  scanMessage.value = ""
  scanResult.value = null
  showScanner.value = true
  await nextTick()
  await startQrScanner()
}

const startQrScanner = async () => {
  if (!navigator.mediaDevices?.getUserMedia) {
    scannerError.value = "Camera scanning requires HTTPS or localhost and browser camera permission."
    return
  }

  try {
    const { Html5Qrcode } = await import("html5-qrcode")
    qrScanner = new Html5Qrcode("booking-qr-reader")
    await qrScanner.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: { width: 240, height: 240 } },
      handleQrDetected,
      () => {}
    )
    scannerIsRunning = true
  } catch (error) {
    scannerError.value = "Could not start the camera. Allow camera access, then try again."
    await stopQrScanner()
  }
}

const handleQrDetected = async (decodedText) => {
  if (isProcessingScan.value) return
  isProcessingScan.value = true
  scannerError.value = ""

  try {
    await stopQrScanner()
    const response = await api.post("/admin/rentals/verify-qr", {
      qr_payload: decodedText,
      action: scanAction.value,
    })
    if (!response.data?.success) throw new Error("QR verification failed")

    scanResult.value = response.data.rental
    const stage = scanAction.value
    scanMessage.value = response.data.already_verified
      ? `This approved booking was already ${stage}-verified.`
      : stage === "pickup"
        ? "Approved booking verified for pickup."
        : "Vehicle return recorded for this booking."
    await fetchRentals()
  } catch (error) {
    scannerError.value = error.response?.data?.message || "Could not verify this booking QR."
  } finally {
    isProcessingScan.value = false
  }
}

const stopQrScanner = async () => {
  if (!qrScanner) return

  if (scannerIsRunning) {
    try {
      await qrScanner.stop()
    } catch (error) {
      // The camera may already have stopped after a scan callback.
    }
    scannerIsRunning = false
  }

  try {
    qrScanner.clear()
  } catch (error) {
    // Clear is safe to skip if camera setup did not finish.
  }
  qrScanner = null
}

const closeScanner = async () => {
  await stopQrScanner()
  showScanner.value = false
  scanResult.value = null
  scannerError.value = ""
}

/* ================= LIFECYCLE ================= */
onMounted(() => {
  fetchRentals()
  rentalsPollTimer = window.setInterval(() => {
    if (document.visibilityState === "visible") fetchRentals()
  }, 15000)
})

onBeforeUnmount(() => {
  window.clearInterval(rentalsPollTimer)
  stopQrScanner()
})

/* ================= APPROVE ================= */
const approveRental = async (id) => {
  try {
    const response = await api.put(`/admin/rentals/${id}/approve`)

    if (response.data?.success) {
      const rental = rentals.value.find(r => r.id === id)
      if (rental) rental.status = "approved"
    }

  } catch (error) {
    console.error(error)
    alert(error.response?.data?.message || "Failed to approve rental")
  }
}

/* ================= DENY (REMOVE ROW) ================= */
const denyRental = async (id) => {
  try {
    const response = await api.put(`/admin/rentals/${id}/deny`)

    if (response.data?.success) {
      rentals.value = rentals.value.filter(r => r.id !== id)
    }

  } catch (error) {
    console.error(error)
    alert("Failed to deny rental")
  }
}

/* ================= RECEIPT MODAL ================= */
const openReceipt = (rental) => {
  selectedReceipt.value = rental.proof_file || "No receipt uploaded"
  selectedReceiptUrl.value = rental.proof_url || ""
  showReceiptModal.value = true
}

const closeReceipt = () => {
  selectedReceipt.value = ""
  selectedReceiptUrl.value = ""
  showReceiptModal.value = false
}

/* ================= HELPERS ================= */
const isImage = (url) => /\.(jpg|jpeg|png|gif|webp)$/i.test(url)

const formatDate = (date) => {
  if (!date) return "N/A"

  return new Date(date).toLocaleString("en-PH", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}
</script>

<style scoped>
.rental-duration { display:block; margin-top:4px; color:var(--text-muted); font-size:.72rem; }
.rent-page {
  box-sizing: border-box;
  position: fixed;
  top: 80px;
  left: 317px;
  width: calc(100vw - 317px);
  height: calc(100vh - 80px);
  padding: 30px;
  background: #0f0f0f;
  overflow-y: auto;
  overflow-x: hidden;
}

.main-content {
  min-height: 100%;
  display: flex;
  flex-direction: column;
}

.content-section {
  margin-top: 10px;
  background: rgba(20, 20, 20, 0.9);
  border-radius: 14px;
  padding: 25px;
  overflow-x: auto;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255,255,255,0.05);
}

/* Table */

table {
  width: 100%;
  min-width: 980px;
  border-collapse: collapse;
  background: rgba(255,255,255,0.03);
  border-radius: 12px;
  overflow: hidden;
}

thead {
  background: rgba(255,255,255,0.06);
}

th {
  text-align: left;
  padding: 14px 18px;
  font-size: 13px;
  font-weight: 600;
  color: #bbb;
  letter-spacing: .5px;
}

td {
  padding: 14px 18px;
  font-size: 14px;
  color: #e4e4e4;
}

tbody tr {
  border-bottom: 1px solid rgba(255,255,255,0.05);
  transition: 0.2s ease;
}

tbody tr:hover {
  background: rgba(255,255,255,0.04);
}

/* Receipt */

.receipt-link {
  color: #4da3ff;
  cursor: pointer;
  text-decoration: none;
  font-weight: 500;
}

.receipt-link:hover {
  text-decoration: underline;
}

/* Status */

.status-cell {
  width: 130px;
}

.status-badge {
  padding: 5px 14px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .4px;
}

.status-pending {
  background: rgba(255,193,7,.15);
  color: #ffc107;
}

.status-approved {
  background: rgba(40,167,69,.15);
  color: #28a745;
}

.status-denied {
  background: rgba(220,53,69,.15);
  color: #dc3545;
}

/* Buttons */

.approveBtn {
  background: #22c55e;
  border: none;
  padding: 7px 14px;
  border-radius: 6px;
  color: white;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: .2s ease;
}

.approveBtn:hover {
  background: #16a34a;
}

.denyBtn {
  background: #ef4444;
  border: none;
  padding: 7px 14px;
  border-radius: 6px;
  color: white;
  cursor: pointer;
  margin-left: 8px;
  font-size: 13px;
  font-weight: 600;
  transition: .2s ease;
}

.denyBtn:hover {
  background: #dc2626;
}

.processed-text {
  color: #888;
  font-size: 13px;
  font-weight: 500;
}

/* Modal */

#receiptModal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,.75);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.receipt-placeholder {
  background: #1a1a1a;
  padding: 30px;
  border-radius: 12px;
  max-width: 650px;
  width: 100%;
  color: white;
  box-shadow: 0 0 40px rgba(0,0,0,.6);
}

.receipt-placeholder h3 {
  margin-bottom: 15px;
}

.closeReceipt {
  position: absolute;
  top: 25px;
  right: 40px;
  color: white;
  font-size: 28px;
  cursor: pointer;
}

.receipt-image {
  max-width: 100%;
  margin-top: 20px;
  border-radius: 8px;
}

.receipt-file-box a {
  color: #4da3ff;
  text-decoration: none;
}

.receipt-file-box a:hover {
  text-decoration: underline;
}

/* Empty */

.empty-state {
  text-align: center;
  padding: 30px;
  color: #888;
  font-size: 14px;
}

.rent-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 18px;
  color: #fff;
}

.rent-toolbar h2 {
  margin: 0 0 5px;
  color: #ffd700;
  font-size: 1.35rem;
}

.rent-toolbar p {
  margin: 0;
  color: #aaa;
  font-size: 0.9rem;
}

.scan-button,
.verify-discount-btn,
.reject-discount-btn,
.scanner-secondary {
  border: 0;
  border-radius: 8px;
  padding: 9px 13px;
  font-weight: 700;
  cursor: pointer;
}

.scan-button {
  flex: 0 0 auto;
  background: #ffd700;
  color: #171717;
}

.scan-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.return-scan-button {
  background: #86efac;
}

td {
  vertical-align: middle;
}

.discount-kind,
.discount-amount {
  display: block;
  margin-bottom: 5px;
}

.discount-amount {
  color: #86efac;
}

.discount-pending,
.discount-verified,
.discount-rejected {
  display: inline-block;
  margin-top: 5px;
}

.discount-pending {
  background: rgba(255, 193, 7, .15);
  color: #ffd76a;
}

.discount-verified {
  background: rgba(34, 197, 94, .15);
  color: #86efac;
}

.discount-rejected {
  background: rgba(239, 68, 68, .15);
  color: #fca5a5;
}

.muted {
  color: #888;
}

.pickup-verified {
  color: #86efac;
  font-size: 0.82rem;
}

.discount-actions {
  display: flex;
  gap: 6px;
  margin-top: 9px;
}

.verify-discount-btn,
.reject-discount-btn {
  padding: 6px 9px;
  font-size: 0.75rem;
}

.verify-discount-btn {
  background: #22c55e;
  color: #052e16;
}

.reject-discount-btn {
  background: #7f1d1d;
  color: #fff;
}

.discount-actions button:disabled {
  opacity: .4;
  cursor: not-allowed;
}

.scanner-overlay {
  position: fixed;
  inset: 0;
  z-index: 11000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 18px;
  background: rgba(0, 0, 0, .78);
}

.scanner-modal {
  position: relative;
  width: min(100%, 520px);
  max-height: min(90vh, 780px);
  overflow-y: auto;
  padding: 28px 22px 20px;
  border: 1px solid rgba(255, 215, 0, .2);
  border-radius: 16px;
  background: #181818;
  color: #fff;
  box-shadow: 0 18px 50px rgba(0, 0, 0, .55);
}

.scanner-modal h2 {
  margin: 0 36px 7px 0;
  color: #ffd700;
}

.scanner-intro {
  margin: 0 0 18px;
  color: #bbb;
}

.scanner-close {
  position: absolute;
  top: 12px;
  right: 14px;
  border: 0;
  background: transparent;
  color: #fff;
  font-size: 28px;
  cursor: pointer;
}

.qr-reader {
  width: 100%;
  min-height: 220px;
  overflow: hidden;
  border-radius: 12px;
  background: #080808;
}

.qr-reader video {
  border-radius: 12px;
}

.scanner-error {
  margin: 14px 0;
  color: #ffb3b3;
}

.scan-result {
  margin-top: 14px;
  padding: 16px;
  border: 1px solid rgba(34, 197, 94, .25);
  border-radius: 12px;
  background: rgba(34, 197, 94, .06);
}

.scan-success {
  color: #86efac;
  font-weight: 700;
}

.scan-result dl {
  margin: 12px 0;
}

.scan-result dl div {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, .08);
}

.scan-result dt {
  color: #aaa;
}

.scan-result dd {
  margin: 0;
  text-align: right;
}

.id-reminder {
  color: #ffe18a;
  font-size: .9rem;
  line-height: 1.5;
}

.scan-stage-note {
  color: #86efac;
  font-size: .9rem;
}

.scanner-secondary {
  margin-top: 14px;
  background: #303030;
  color: #fff;
}

.availability-cell { min-width: 150px; }
.conflict-badge { display:block; width:max-content; margin-bottom:7px; padding:5px 8px; border-radius:999px; background:rgba(239,68,68,.16); color:#fca5a5; font-size:.7rem; font-weight:700; }
.resolve-button { border:0; border-radius:7px; padding:6px 10px; background:#3b82f6; color:white; font-weight:700; cursor:pointer; }
.resolver-overlay { position:fixed; inset:0; z-index:12000; display:flex; align-items:center; justify-content:center; padding:18px; background:rgba(0,0,0,.78); }
.resolver-modal { position:relative; width:min(100%,560px); max-height:90vh; overflow-y:auto; padding:28px; border:1px solid rgba(96,165,250,.28); border-radius:16px; background:#171b22; color:#eef3fa; box-shadow:0 20px 60px rgba(0,0,0,.55); }
.resolver-modal h2 { margin:0 36px 8px 0; color:#bfdbfe; }
.resolver-summary { margin:0 0 18px; color:#aab6c6; }
.resolver-close { position:absolute; top:10px; right:14px; border:0; background:transparent; color:#fff; font-size:28px; cursor:pointer; }
.resolver-option { display:flex; align-items:flex-start; gap:11px; margin:10px 0; padding:12px; border:1px solid rgba(255,255,255,.1); border-radius:10px; background:rgba(255,255,255,.035); cursor:pointer; }
.resolver-option input,.resolver-check input { margin-top:3px; accent-color:#3b82f6; }
.resolver-option strong,.resolver-option small { display:block; }
.resolver-option small { margin-top:3px; color:#9ba8b9; line-height:1.4; }
.resolver-field { margin:0 0 12px 30px; }
.resolver-field label { display:block; margin-bottom:5px; color:#c8d3e1; font-size:.85rem; }
.resolver-field input,.resolver-field select { box-sizing:border-box; width:100%; padding:10px 11px; border:1px solid #3d4b5d; border-radius:8px; background:#0e131a; color:#f2f5f9; }
.resolver-refresh { margin-top:7px; padding:5px 8px; border:0; border-radius:6px; background:#25364b; color:#cfe3ff; cursor:pointer; }
.resolver-check { display:flex; align-items:flex-start; gap:8px; margin:15px 2px; color:#c8d3e1; font-size:.88rem; }
.resolver-error { color:#fca5a5; font-size:.88rem; }
.resolver-actions { display:flex; justify-content:flex-end; gap:9px; margin-top:18px; }
.resolver-actions button { border:0; border-radius:8px; padding:9px 14px; font-weight:700; cursor:pointer; }
.resolver-save { background:#3b82f6; color:white; }
.resolver-save:disabled { opacity:.55; cursor:wait; }
.resolver-cancel { background:#303a47; color:#e4eaf2; }

@media (max-width: 900px) {
  .rent-toolbar {
    align-items: flex-start;
    flex-direction: column;
  }

  .scan-actions {
    width: 100%;
  }
}

</style>
