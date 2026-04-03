<template>
  <div v-if="show" class="popup-overlay">
    <div class="popup-card">
      <h2>Welcome to Your AI-Powered Creative Assistant</h2>
  
  <p>
    Upload an image and instantly get <b>high-quality, ready-to-use prompts</b> for other Ai tools like 
  </p>
  
  
  <p>
    <b style="text-decoration: underline; color: var(--accent);">Demo Access:</b> You get <b style="text-decoration: underline;">5 prompt generations per day</b> to explore the platform.
     <br>
     <sub style="color: var(--accent); font-size:20px;font-family:cursive;"> ~NR </sub>
  </p>
      <button class="btn btn-primary" @click="$emit('close')">OK</button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean
});

defineEmits(['close']);
</script>

<style scoped>
.popup-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.popup-card {
  background: #fff;
  border-radius: 12px;
  padding: 2rem 2.5rem;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
  text-align: center;
  max-width: 90vw;
}
.popup-card h3 {
    color: var(--accent);
  margin-bottom: 0.7rem;
}
.popup-card h2 {
    color: var(--accent);
  margin-bottom: 1.6rem;
}
.popup-card p {
    color: var(--text-secondary);
  margin-bottom: 1.2rem;
  font-size: 0.8rem;
}

</style>
