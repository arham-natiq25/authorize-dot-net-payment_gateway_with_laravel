<!-- CardForm.vue -->

<template>
    <div class="col-md-6">
        <div class="mb-3">
          <label for="cardNumber" class="form-label">Card Number</label>
          <input type="text" class="form-control" id="cardNumber" maxlength="16" minlength="16" v-model="cardNumber" placeholder="Enter card number" required>
        </div>

        <div class="mb-3">
          <label for="cvv" class="form-label">CVV</label>
          <input type="text" class="form-control" id="cvv" maxlength="3" minlength="3" v-model="cvv" placeholder="Enter CVV" required>
        </div>

        <div class="mb-3">
          <label for="expiryMonth" class="form-label">Expiry Month</label>
          <select class="form-select" id="expiryMonth" v-model="expiryMonth" required>
            <option value="" disabled>Select month</option>
            <option v-for="month in months" :key="month" :value="month">{{ month }}</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="expiryYear" class="form-label">Expiry Year</label>
          <select class="form-select" id="expiryYear" v-model="expiryYear" required>
            <option value="" disabled>Select year</option>
            <option v-for="year in dynamicYears" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
  </template>

  <script>
  export default {
    data() {
      return {
        cardNumber: '',
        cvv: '',
        expiryMonth: '',
        expiryYear: '',
        months: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
        years: ['2023', '2024', '2025', '2026', '2027', '2028', '2029', '2030']
      };

    },
    computed: {
    dynamicYears() {
      const currentYear = new Date().getFullYear();
      const futureYears = Array.from({ length: 15 }, (_, index) => currentYear + index);
      return futureYears.map(String); // Convert years to strings
    }
    },
    methods: {
      submitForm() {
        // You can perform form submission logic here
        console.log('Form submitted:', this.cardNumber, this.cvv, this.expiryMonth, this.expiryYear);
      }
    }
  };
  </script>

  <style scoped>
  /* Add any component-specific styles here */
  </style>
