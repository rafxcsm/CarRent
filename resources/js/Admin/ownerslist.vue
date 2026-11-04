<template>
  <div class="owners-page">
    <main class="main-content">
      <section class="content-section">
        <!-- Search Bar -->
        <div class="search-bar">
          <input
            v-model="search"
            @input="fetchUsers"
            type="text"
            placeholder="Search..."
          />
        </div>

        <!-- Users Table -->
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
              v-for="user in users"
              :key="user.id"
              :class="{ 'selected-row': selectedUser?.id === user.id }"
              @click="selectUser(user)"
            >
              <td>{{ user.id }}</td>
              <td>{{ user.full_name }}</td>
<td>{{ user.contact_no }}</td>
<td>{{ user.address }}</td>
<td>{{ user.license_no }}</td>
            </tr>

            <tr v-if="users.length === 0">
              <td colspan="5" class="empty-row">No users found.</td>
            </tr>
          </tbody>
        </table>

        <!-- Delete Button -->
        <div class="action-buttons">
          <button class="button-danger" @click="openDeleteModal">Delete</button>
        </div>
      </section>
    </main>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="modal" @click.self="closeDeleteModal">
      <div class="modal-content small">
        <h3>Are you sure you want to delete this user?</h3>
        <div class="modal-buttons">
          <button id="confirmDelete" class="button-danger" @click="deleteUser">Yes</button>
          <button class="closeBtn button-secondary" @click="closeDeleteModal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const search = ref('')
const users = ref([])
const selectedUser = ref(null)
const showDeleteModal = ref(false)

const fetchUsers = async () => {

  try {

    // ✅ CORRECT ADMIN ROUTE
    const response = await api.get('/admin/owners', {
      params: {
        search: search.value
      }
    })

    if (response.data.success) {

      // ✅ CORRECT RESPONSE FIELD
      users.value = response.data.data || []

    } else {

      users.value = []

    }

  } catch (err) {

    console.error('Failed to fetch users', err)

    users.value = []

    alert(
      err.response?.data?.message ||
      'Failed to fetch users.'
    )

  }
}

const selectUser = (user) => {
  selectedUser.value = user
}

const openDeleteModal = () => {

  if (!selectedUser.value) {

    alert('Please select a user to delete.')

    return
  }

  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
}

const deleteUser = async () => {

  if (!selectedUser.value) return

  try {

    // ✅ CORRECT DELETE ROUTE
    await api.delete(
      `/admin/owners/${selectedUser.value.id}`
    )

    users.value = users.value.filter(
      user => user.id !== selectedUser.value.id
    )

    selectedUser.value = null

    closeDeleteModal()

  } catch (err) {

    console.error('Failed to delete user', err)

    alert(
      err.response?.data?.message ||
      'Failed to delete user.'
    )

  }
}

onMounted(() => {
  fetchUsers()
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

th,
td {
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

.empty-row {
  text-align: center;
  color: rgba(255, 255, 255, 0.7);
  padding: 20px;
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
  background: rgba(0, 0, 0, 0.7);
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

.modal-content h3 {
  color: white;
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
