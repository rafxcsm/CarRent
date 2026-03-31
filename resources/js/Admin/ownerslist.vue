<template>
  <div class="owners-page">
    <main class="main-content">
      <section class="content-section">
        <!-- Search Bar -->
        <div class="search-bar">
          <input v-model="search" @input="fetchOwners" type="text" placeholder="Search..." />
        </div>

        <!-- Owners Table -->
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Full Name</th>
              <th>Contact No.</th>
              <th>Address</th>
              <th>License No.</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="owner in owners"
              :key="owner.id"
              :class="{ 'selected-row': selectedOwner?.id === owner.id }"
              @click="selectOwner(owner)"
            >
              <td>{{ owner.id }}</td>
              <td>{{ owner.full_name }}</td>
              <td>{{ owner.contact_no }}</td>
              <td>{{ owner.address }}</td>
              <td>{{ owner.license_no }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Delete Button -->
        <div class="action-buttons">
          <button @click="openDeleteModal">Delete</button>
        </div>
      </section>
    </main>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="modal" @click.self="closeDeleteModal">
      <div class="modal-content small">
        <h3>Are you sure you want to delete this owner?</h3>
        <div class="modal-buttons">
          <button id="confirmDelete" @click="deleteOwner">Yes</button>
          <button class="closeBtn" @click="closeDeleteModal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const search = ref('')
const owners = ref([])
const selectedOwner = ref(null)
const showDeleteModal = ref(false)

const fetchOwners = async () => {
  try {
    const response = await axios.get('/owners', { params: { search: search.value } })
    if (response.data.success) owners.value = response.data.data
  } catch (err) {
    console.error('Failed to fetch owners', err)
  }
}

const selectOwner = owner => {
  selectedOwner.value = owner
}

const openDeleteModal = () => {
  if (!selectedOwner.value) {
    alert('Please select an owner to delete.')
    return
  }
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
}

const deleteOwner = async () => {
  if (!selectedOwner.value) return
  try {
    await axios.delete(`/api/owners/${selectedOwner.value.id}`)
    owners.value = owners.value.filter(o => o.id !== selectedOwner.value.id)
    selectedOwner.value = null
    closeDeleteModal()
  } catch (err) {
    console.error('Failed to delete owner', err)
  }
}

onMounted(() => {
  fetchOwners()
})
</script>

<style scoped>
.owners-page {
  position: fixed;
  top: 80px;
  left: 317px;
  width: calc(100vw - 317px);
  height: calc(100vh - 80px);
  padding: 20px;
  background: black;
  overflow-y: auto;
}

.main-content {
  display: flex;
  flex-direction: column;
}

.content-section {
  background: rgba(255, 255, 255, 0.05);
  padding: 25px;
  border-radius: 15px;
}

.search-bar {
  margin-bottom: 15px;
}

.search-bar input {
  width: 100%;
  max-width: 350px;
  padding: 8px 12px;
  border-radius: 6px;
  border: none;
  outline: none;
  font-size: 0.9rem;
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  background: rgba(255, 255, 255, 0.05);
}

thead {
  background: rgba(255, 255, 255, 0.1);
}

th, td {
  text-align: left;
  padding: 10px 12px;
  font-size: 0.9rem;
  color: white;
}

tbody tr {
  cursor: pointer;
  transition: background 0.3s;
}

tbody tr:hover {
  background: rgba(255, 255, 255, 0.15);
}

.selected-row {
  background: rgba(255, 215, 0, 0.3) !important;
}

.action-buttons {
  display: flex;
  justify-content: flex-end;
}

.action-buttons button {
  background: #ffd700;
  color: black;
  padding: 8px 18px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}

.action-buttons button:hover {
  background: white;
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #111;
  padding: 25px 35px;
  border-radius: 12px;
  width: 320px;
  text-align: center;
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
}

.modal-buttons button:hover {
  background: white;
}
</style>