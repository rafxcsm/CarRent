<template>
  <div class="owners-page">
    <div class="background"></div>

    <main class="main-content">
      <section class="content-section">
        <div class="search-bar">
          <input v-model="search" type="text" placeholder="Search..." />
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Full Name</th>
              <th>Gender</th>
              <th>Birthdate</th>
              <th>Contact No.</th>
              <th>Address</th>
              <th>License No.</th>
              <th>Status</th>
              <th>Date Created</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="owner in filteredOwners"
              :key="owner.id"
              :class="{ 'selected-row': selectedOwner?.id === owner.id }"
              @click="selectOwner(owner)"
            >
              <td>{{ owner.id }}</td>
              <td>{{ owner.full_name }}</td>
              <td>{{ owner.gender }}</td>
              <td>{{ owner.birthdate }}</td>
              <td>{{ owner.contact_no }}</td>
              <td>{{ owner.address }}</td>
              <td>{{ owner.license_no }}</td>
              <td>{{ owner.status }}</td>
              <td>{{ owner.created_at }}</td>
            </tr>
          </tbody>
        </table>

        <div class="action-buttons">
          <button id="deleteBtn" @click="openDeleteModal">Delete</button>
        </div>
      </section>
    </main>

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
import { computed, ref } from 'vue'

const search = ref('')
const selectedOwner = ref(null)
const showDeleteModal = ref(false)

const owners = ref([
  {
    id: 1,
    full_name: 'Juan Dela Cruz',
    gender: 'Male',
    birthdate: '1995-03-12',
    contact_no: '09123456789',
    address: 'Surigao City',
    license_no: 'N01-23-456789',
    status: 'Active',
    created_at: '03/10/2026',
  },
  {
    id: 2,
    full_name: 'Maria Santos',
    gender: 'Female',
    birthdate: '1998-07-25',
    contact_no: '09987654321',
    address: 'Butuan City',
    license_no: 'N98-76-543210',
    status: 'Active',
    created_at: '03/11/2026',
  },
  {
    id: 3,
    full_name: 'Mark Reyes',
    gender: 'Male',
    birthdate: '1992-11-08',
    contact_no: '09112223344',
    address: 'Bislig City',
    license_no: 'N12-34-567890',
    status: 'Inactive',
    created_at: '03/12/2026',
  },
])

const filteredOwners = computed(() => {
  const term = search.value.toLowerCase().trim()
  if (!term) return owners.value

  return owners.value.filter((owner) =>
    [
      owner.id,
      owner.full_name,
      owner.gender,
      owner.birthdate,
      owner.contact_no,
      owner.address,
      owner.license_no,
      owner.status,
      owner.created_at,
    ]
      .join(' ')
      .toLowerCase()
      .includes(term)
  )
})

const selectOwner = (owner) => {
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

const deleteOwner = () => {
  if (!selectedOwner.value) return

  owners.value = owners.value.filter((owner) => owner.id !== selectedOwner.value.id)
  selectedOwner.value = null
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

.owners-page {
  min-height: calc(100vh - 80px);
  background-color: #0e0e0e;
  color: white;
  position: relative;
  margin-left: 308px;
}

.background {
  position: absolute;
  inset: 0;
  opacity: 0.3;
  filter: brightness(50%);
  z-index: 0;
}

.main-content {
  min-height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 1;
}

.content-section {
  margin-top: 40px;
  background: rgba(0, 0, 0, 0.6);
  border-radius: 15px;
  padding: 40px;
  backdrop-filter: blur(3px);
  flex: 1;
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

.search-bar input {
  flex: 1;
  max-width: 320px;
  padding: 8px 15px;
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

.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 20px;
  margin-top: 25px;
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
  background: rgba(0,0,0,0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-content {
  background: #111;
  color: white;
  padding: 25px 35px;
  border-radius: 12px;
  width: 350px;
  text-align: center;
  box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
}

.modal-content.small {
  width: 320px;
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

.selected-row {
  background: rgba(255, 215, 0, 0.3) !important;
}
</style>