<template>
  <div class="dashboard-page">
    <section class="dashboard-layout">
      <div class="dashboard-map">
        <h2>Our Rental Shop Location</h2>
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1007.2400853653274!2d125.49589573174583!3d9.787397809471626!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x330136bee35cc3ef%3A0x9b7f2d5a0bac57b5!2sNarciso%20Street%2C%20Surigao%2C%20Surigao%20del%20Norte!5e0!3m2!1sen!2sph!4v1765790499257!5m2!1sen!2sph"
          allowfullscreen
          loading="lazy"
        ></iframe>
      </div>

      <div class="dashboard-cards">
        <div class="card">
          <div class="circle" style="--value: 90">
            <span>90%</span>
          </div>
          <p>Total Customers</p>
        </div>

        <div class="card">
          <div class="circle" style="--value: 80">
            <span>80%</span>
          </div>
          <p>Daily Vehicles Summary</p>
        </div>

        <div class="card">
          <div class="circle" style="--value: 95">
            <span>95%</span>
          </div>
          <p>Daily Sales Summary</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
</script>

<style scoped>
.dashboard-page {
  min-height: 80%;
  margin-left: 308px;
  padding: 50px 30px 30px 30px;
  background: black;
}

.dashboard-layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 25px;
  margin-top: 10px;
  align-items: stretch;
}

.dashboard-map {
  background: #111;
  padding: 40px;
  border-radius: 12px;
}

.dashboard-map h2 {
  font-size: 1.3rem;
  font-weight: 700;
  margin-top: 0px;
  color: white;
}

.dashboard-map iframe {
  width: 100%;
  height: 100%;
  min-height: 350px;
  border-radius: 10px;
  border: none;
}

.dashboard-cards {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 20px;
}

.card {
  background: #111;
  padding: 20px;
  border-radius: 12px;
  text-align: center;
}

.card p {
  font-size: 1rem;
  color: #ccc;
}

.circle {
  --size: 120px;
  width: var(--size);
  height: var(--size);
  border-radius: 50%;
  background: conic-gradient(#ffd700 calc(var(--value) * 1%), #333 0%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 15px auto;
  position: relative;
  color: #ffd700;
  font-weight: 700;
  font-size: 1.4rem;
}

.circle::before {
  content: '';
  position: absolute;
  width: calc(var(--size) - 20px);
  height: calc(var(--size) - 20px);
  background: #0e0e0e;
  border-radius: 50%;
  z-index: 0;
}

.circle span {
  position: relative;
  z-index: 1;
}

@media (max-width: 992px) {
  .dashboard-layout {
    grid-template-columns: 1fr;
  }
}
</style>