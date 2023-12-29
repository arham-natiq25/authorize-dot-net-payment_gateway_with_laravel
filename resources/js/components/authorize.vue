<!-- CardForm.vue -->

<template>
    <div v-if="successMessage" class="alert alert-success" role="alert">
        {{ successMessage }}
      </div>

      <!-- Bootstrap alert for error -->
      <div v-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>

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
        <button type="submit" class="btn btn-primary" @click="submitForm">Submit</button>
    </div>

    <div>
        <div class="row">
            <div class="col-md-8">
                <b><label>Select Card</label></b>

                <div v-for="card in cards" :key="card.id">
                    <input type="radio" :id="'card_' + card.id" name="selectedCard" class="m-2" :value="card" v-model="selectedCard">
                    <label :for="'card_' + card.id" class="m-2">
                        **** ****  {{ card.last_four_digit }}
                        <span class="ms-5">{{ card.card_type }}</span>
                    </label>
                </div>
            </div>

            <div class="col-md-3 mt-4">
                <button class="btn btn-success" @click="payWithCard">Pay</button>
            </div>
        </div>
    </div>
  </template>

  <script>
  import axios  from "axios";
  export default {
    data() {
      return {
        cardNumber: '',
        cvv: '',
        expiryMonth: '',
        expiryYear: '',
        months: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
        years: ['2023', '2024', '2025', '2026', '2027', '2028', '2029', '2030'],
        cards:[],
        selectedCard:null,
        successMessage: null,
      errorMessage: null,
      };

    },
    computed: {
    dynamicYears() {
      const currentYear = new Date().getFullYear();
      const futureYears = Array.from({ length: 15 }, (_, index) => currentYear + index);
      return futureYears.map(String); // Convert years to strings
    }
    },
    mounted() {
        this.getCards();
    },
    methods: {
        payWithCard() {

        this.successMessage = null;
        this.errorMessage = null;
          const card = {
           card: this.selectedCard,
        }
        axios.post('/payment/card', card)
          .then(response => {
            // Handle the response from the backend
            this.successMessage = 'Backend Response ' + response.data.message;
          })
          .catch(error => {
            // Handle errors
            this.errorMessage =
            'Error making payment: ' +
            (error.response.data.error
              ? error.response.data.error[0].text
              : 'An unexpected error occurred.');
          });
    },
      submitForm() {
        this.successMessage = null;
      this.errorMessage = null;
        const cardDetails = {
        cardNumber: this.cardNumber,
        cvv: this.cvv,
        expiryMonth: this.expiryMonth,
        expiryYear: this.expiryYear
         };
         axios.post('/payment', cardDetails)
        .then(response => {
            this.successMessage = 'Payment successful: ' + response.message;
        })
        .catch(error => {
            this.errorMessage =
            'Error making payment: ' +
            (error.response.data.error
              ? error.response.data.error[0].text
              : 'An unexpected error occurred.');
        });
      },
      getCards(){
        axios.get('/cards').then((res)=>{
            this.cards = res.data
        });
      }
    },

  };
  </script>

  <style scoped>
  /* Add any component-specific styles here */
  </style>
