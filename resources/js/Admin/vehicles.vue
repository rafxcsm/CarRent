<template>
  <div class="vehicles-page">
    <section class="content-section">
      <div class="search-bar">
        <input v-model="search" type="text" placeholder="Search..." />
      </div>

      <div v-if="loading" class="empty-state">Loading vehicles...</div>
      <div v-else-if="error" class="empty-state">{{ error }}</div>

      <table v-else id="vehicleTable">
        <thead>
          <tr>
            <th>Reg No</th>
            <th>Brand</th>
            <th>Color</th>
            <th>Model</th>
            <th>Rate</th>
            <th>Status</th>
            <th>Created</th>
          </tr>
        </thead>

        <tbody v-if="filteredVehicles.length">
          <tr
            v-for="vehicle in filteredVehicles"
            :key="vehicle.id"
            :class="{ selected: selectedVehicle?.id === vehicle.id }"
            @click="selectVehicle(vehicle)"
          >
            <td>{{ vehicle.reg_no }}</td>
            <td>{{ vehicle.brand }}</td>
            <td>{{ vehicle.color }}</td>
            <td>{{ vehicle.model }}</td>
            <td>₱{{ formatRate(vehicle.rate) }}</td>
            <td>{{ vehicle.status }}</td>
            <td>{{ formatDate(vehicle.created_at) }}</td>
          </tr>
        </tbody>

        <tbody v-else>
          <tr>
            <td colspan="7" class="empty-state">No vehicles found.</td>
          </tr>
        </tbody>
      </table>

      <div class="action-buttons">
        <button @click="openAddModal">Add</button>
        <button @click="openDeleteModal">Delete</button>
        <button @click="openUpdateModal">Update</button>
      </div>
    </section>

    <div v-if="showVehicleModal" class="modal" @click.self="closeVehicleModal">
      <div class="modal-content">
        <h2>{{ isEditMode ? 'Update Vehicle' : 'Add New Vehicle' }}</h2>

        <form @submit.prevent="saveVehicle">
          <label>Reg. No:</label>
          <input v-model="vehicleForm.reg_no" type="text" required />

          <label>Brand:</label>
          <input v-model="vehicleForm.brand" type="text" required />

          <label>Color:</label>
          <input v-model="vehicleForm.color" type="text" required />

          <label>Model:</label>
          <input v-model="vehicleForm.model" type="text" required />

          <label>Rental Rate:</label>
          <input v-model.number="vehicleForm.rate" type="number" required />

          <label>Status:</label>
          <select v-model="vehicleForm.status" required>
            <option value="Available">Available</option>
            <option value="Rented">Rented</option>
          </select>

          <label>Vehicle Photo:</label>
          <input type="file" accept="image/*" @change="handleImageChange" />
          <small v-if="isEditMode" class="hint">
            Leave blank if you do not want to change the image.
          </small>

          <div class="modal-buttons">
            <button type="submit" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
            <button type="button" @click="closeVehicleModal">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal" @click.self="closeDeleteModal">
      <div class="modal-content small">
        <h3>Delete this vehicle?</h3>
        <p v-if="selectedVehicle">
          {{ selectedVehicle.reg_no }} - {{ selectedVehicle.brand }}
        </p>

        <div class="modal-buttons">
          <button
            type="button"
            class="danger-btn"
            @click="deleteVehicle"
            :disabled="deleting"
          >
            {{ deleting ? 'Deleting...' : 'Delete' }}
          </button>
          <button type="button" @click="closeDeleteModal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import axios from 'axios'

const search = ref('')
const selectedVehicle = ref(null)

const showVehicleModal = ref(false)
const showDeleteModal = ref(false)
const isEditMode = ref(false)

const vehicles = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')

const vehicleForm = reactive({
  id: null,
  reg_no: '',
  brand: '',
  color: '',
  model: '',
  rate: '',
  status: 'Available',
  image: null,
})

const fetchVehicles = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await axios.get('/admin/vehicles')

    if (response.data?.success) {
      vehicles.value = response.data.vehicles || []
    } else {
      error.value = 'Failed to load vehicles.'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load vehicles.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchVehicles()
})

const filteredVehicles = computed(() => {
  const term = search.value.toLowerCase().trim()

  if (!term) return vehicles.value

  return vehicles.value.filter((vehicle) =>
    [
      vehicle.reg_no,
      vehicle.brand,
      vehicle.color,
      vehicle.model,
      vehicle.rate,
      vehicle.status,
      vehicle.created_at,
    ]
      .join(' ')
      .toLowerCase()
      .includes(term)
  )
})

const formatRate = (rate) => {
  return Number(rate || 0).toLocaleString()
}

const formatDate = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleDateString('en-US')
}

const selectVehicle = (vehicle) => {
  selectedVehicle.value = vehicle
}

const resetForm = () => {
  vehicleForm.id = null
  vehicleForm.reg_no = ''
  vehicleForm.brand = ''
  vehicleForm.color = ''
  vehicleForm.model = ''
  vehicleForm.rate = ''
  vehicleForm.status = 'Available'
  vehicleForm.image = null
}

const handleImageChange = (event) => {
  vehicleForm.image = event.target.files[0] || null
}

const openAddModal = () => {
  isEditMode.value = false
  resetForm()
  showVehicleModal.value = true
}

const openUpdateModal = () => {
  if (!selectedVehicle.value) {
    alert('Select vehicle first')
    return
  }

  isEditMode.value = true
  vehicleForm.id = selectedVehicle.value.id
  vehicleForm.reg_no = selectedVehicle.value.reg_no
  vehicleForm.brand = selectedVehicle.value.brand
  vehicleForm.color = selectedVehicle.value.color
  vehicleForm.model = selectedVehicle.value.model
  vehicleForm.rate = selectedVehicle.value.rate
  vehicleForm.status = selectedVehicle.value.status
  vehicleForm.image = null

  showVehicleModal.value = true
}

const closeVehicleModal = () => {
  showVehicleModal.value = false
}

const openDeleteModal = () => {
  if (!selectedVehicle.value) {
    alert('Select a vehicle first')
    return
  }

  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
}

const saveVehicle = async () => {
  saving.value = true

  try {
    const formData = new FormData()
    formData.append('reg_no', vehicleForm.reg_no)
    formData.append('brand', vehicleForm.brand)
    formData.append('color', vehicleForm.color)
    formData.append('model', vehicleForm.model)
    formData.append('rate', vehicleForm.rate)
    formData.append('status', vehicleForm.status)

    if (vehicleForm.image) {
      formData.append('image', vehicleForm.image)
    }

    if (isEditMode.value) {
      formData.append('_method', 'POST')

      await axios.post(`/admin/vehicles/${vehicleForm.id}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
    } else {
      await axios.post('/admin/vehicles', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
    }

    await fetchVehicles()
    closeVehicleModal()
    resetForm()
    selectedVehicle.value = null
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) {
      const firstError = Object.values(errors)[0]?.[0]
      alert(firstError || 'Failed to save vehicle.')
    } else {
      alert(err.response?.data?.message || 'Failed to save vehicle.')
    }
  } finally {
    saving.value = false
  }
}

const deleteVehicle = async () => {
  if (!selectedVehicle.value) return

  deleting.value = true

  try {
    await axios.delete(`/admin/vehicles/${selectedVehicle.value.id}`)
    selectedVehicle.value = null
    await fetchVehicles()
    closeDeleteModal()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete vehicle.')
  } finally {
    deleting.value = false
  }
}
</script>

<style scoped>
.vehicles-page {
  box-sizing: border-box;
  position: fixed;
  top: 80px;
  left: 317px;
  width: calc(100vw - 317px);
  height: calc(100vh - 80px);
  padding: 30px;
  background: black;
  overflow-y: auto;
  overflow-x: hidden;
}

.content-section {
  background: rgba(0, 0, 0, 0.6);
  border-radius: 15px;
  padding: 40px;
  backdrop-filter: blur(3px);
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

.search-bar input {
  width: 100%;
  max-width: 350px;
  padding: 8px 15px;
  border-radius: 6px;
  border: none;
  outline: none;
  font-size: 0.9rem;
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.search-bar input::placeholder {
  color: #ddd;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 25px;
}

thead {
  background: rgba(255, 255, 255, 0.15);
}

th,
td {
  text-align: left;
  padding: 12px 18px;
  font-size: 0.95rem;
  color: white;
}

th {
  font-weight: 600;
  letter-spacing: 0.5px;
}

tbody tr {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  transition: background 0.3s;
  cursor: pointer;
}

tbody tr:hover {
  background: rgba(255, 255, 255, 0.1);
}

tbody tr.selected {
  outline: 1px solid #ffd700;
  background: rgba(255, 215, 0, 0.1);
}

.empty-state {
  text-align: center;
  color: #ccc;
}

.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 20px;
}

.action-buttons button {
  background: #fff;
  color: #000;
  border: none;
  padding: 8px 20px;
  font-weight: 600;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-buttons button:hover {
  background: #ffd700;
  color: #000;
  box-shadow: 0 0 10px #ffd700;
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #111;
  color: white;
  padding: 25px 35px;
  border-radius: 12px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
}

.modal-content.small {
  width: 320px;
  text-align: center;
}

.modal-content h2,
.modal-content h3 {
  margin-bottom: 20px;
  text-align: center;
  color: #ffd700;
}

.modal-content p {
  text-align: center;
  color: #ddd;
  margin-bottom: 10px;
}

.modal-content label {
  display: block;
  margin-top: 10px;
  font-weight: 600;
}

.modal-content input,
.modal-content select {
  width: 100%;
  padding: 8px 10px;
  margin-top: 5px;
  border-radius: 6px;
  border: none;
  background: rgba(255, 255, 255, 0.712);
  color: black;
  outline: none;
  box-sizing: border-box;
}

.hint {
  display: block;
  margin-top: 8px;
  color: #ccc;
  font-size: 0.85rem;
}

.modal-buttons {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 20px;
}

.modal-buttons button {
  background: #ffd700;
  color: black;
  font-weight: 600;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-buttons button:hover {
  background: white;
}

.modal-buttons button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.danger-btn {
  background: red !important;
  color: white !important;
}

@media (max-width: 992px) {
  .vehicles-page {
    left: 0;
    width: 100vw;
  }

  .content-section {
    padding: 20px;
  }

  th,
  td {
    padding: 10px 12px;
    font-size: 0.85rem;
  }

  .action-buttons {
    flex-wrap: wrap;
  }
}
</style>