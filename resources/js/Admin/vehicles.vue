<template>
  <div class="vehicles-page">
    <section class="content-section">
      <div class="search-bar">
        <input v-model="search" type="text" placeholder="Search..." />
      </div>

      <table id="vehicleTable">
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

        <tbody>
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
            <td>{{ vehicle.created_at }}</td>
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

          <div class="modal-buttons">
            <button type="submit">Save</button>
            <button type="button" @click="closeVehicleModal">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal" @click.self="closeDeleteModal">
      <div class="modal-content small">
        <h3>Delete this vehicle?</h3>

        <div class="modal-buttons">
          <button type="button" class="danger-btn" @click="deleteVehicle">
            Delete
          </button>
          <button type="button" @click="closeDeleteModal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'

const search = ref('')
const selectedVehicle = ref(null)

const showVehicleModal = ref(false)
const showDeleteModal = ref(false)
const isEditMode = ref(false)

const vehicles = ref([
  {
    id: 1,
    reg_no: 'ABC-1234',
    brand: 'Toyota',
    color: 'White',
    model: 'Vios',
    rate: 2500,
    status: 'Available',
    created_at: '03/18/2026',
  },
  {
    id: 2,
    reg_no: 'XYZ-5678',
    brand: 'Mitsubishi',
    color: 'Black',
    model: 'Montero',
    rate: 4500,
    status: 'Rented',
    created_at: '03/16/2026',
  },
  {
    id: 3,
    reg_no: 'LMN-2468',
    brand: 'Honda',
    color: 'Red',
    model: 'City',
    rate: 2800,
    status: 'Available',
    created_at: '03/15/2026',
  },
])

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

const formatRate = (rate) => Number(rate).toLocaleString()

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

const saveVehicle = () => {
  if (isEditMode.value) {
    const index = vehicles.value.findIndex((v) => v.id === vehicleForm.id)
    if (index !== -1) {
      vehicles.value[index] = {
        ...vehicles.value[index],
        reg_no: vehicleForm.reg_no,
        brand: vehicleForm.brand,
        color: vehicleForm.color,
        model: vehicleForm.model,
        rate: vehicleForm.rate,
        status: vehicleForm.status,
      }
    }
  } else {
    vehicles.value.push({
      id: Date.now(),
      reg_no: vehicleForm.reg_no,
      brand: vehicleForm.brand,
      color: vehicleForm.color,
      model: vehicleForm.model,
      rate: vehicleForm.rate,
      status: vehicleForm.status,
      created_at: new Date().toLocaleDateString('en-US'),
    })
  }

  closeVehicleModal()
  resetForm()
}

const deleteVehicle = () => {
  if (!selectedVehicle.value) return

  vehicles.value = vehicles.value.filter(
    (v) => v.id !== selectedVehicle.value.id
  )
  selectedVehicle.value = null
  closeDeleteModal()
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

.vehicles-page {
  min-height: 100%;
  background: #0e0e0e;
  margin-left: 308px;
  padding: 150px 100px 100px 100px;

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

.danger-btn {
  background: red !important;
  color: white !important;
}

@media (max-width: 992px) {
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