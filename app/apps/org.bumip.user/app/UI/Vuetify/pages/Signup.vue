<template>
  <div class="pt-10">
    <div></div>
    <div>
      <v-container>
        <v-stepper
          v-model="e6"
          :class="
            e6 == 8
              ? 'elevation-0 mx-auto mt-5 brand-form'
              : 'elevation-0 mx-auto mt-5 brand-form'
          "
          style="max-width: 500px"
        >
          <div class="text-center mt-5" v-if="!listing">
            YOURAPP
            <div class="mt-12 mb-6" v-if="e6 < 4">
              <h3 class="display-2 font-weight-bold">{{ btr("Signup") }}</h3>
            </div>
          </div>
          <v-form>
            <v-stepper-items>
              <v-stepper-content step="1">
                <div>
                  <v-text-field
                    class="mt-1"
                    autocomplete="nope"
                    outlined
                    v-model="email"
                    :error="error"
                    :error-messages="errorMessage"
                    :rules="[(v) => !!v || btr('Please fill this field')]"
                    label="E-mail"
                    required
                  ></v-text-field>
                  <div>
                    <v-checkbox
                      color="primary"
                      :label="
                        btr('I want to receive marketing e-mails from Company')
                      "
                      v-model="marketingApproval"
                      value="1"
                      hide-details
                    ></v-checkbox>
                  </div>
                </div>
                <v-btn
                  block
                  depressed
                  large
                  :disabled="!email"
                  :loading="loading"
                  color="#40929d"
                  class="white--text mt-5 font-weight-regular"
                  @click.prevent="checkSignup(2)"
                  >{{ btr("Next") }}</v-btn
                >
              </v-stepper-content>
              <v-stepper-content step="2">
                <div>
                  <v-text-field
                    ref="emailInput"
                    class="mt-1"
                    outlined
                    v-model="password"
                    :rules="[(v) => !!v || btr('Please fill this field')]"
                    :label="btr('password')"
                    required
                    :append-icon="isPassword ? 'mdi-eye' : 'mdi-eye-off'"
                    @click:append="() => (isPassword = !isPassword)"
                    :type="isPassword ? 'password' : 'text'"
                  ></v-text-field>
                  <div class="mb-3">
                    <strong v-if="!passwordOk">{{
                      btr("Create your password")
                    }}</strong>
                    <strong v-if="passwordOk" style="color: #40929d">{{
                      btr("Password Safe")
                    }}</strong>
                  </div>
                  <v-input
                    class="g-confirm"
                    :prepend-icon="
                      password.length >= 8
                        ? 'mdi-check-circle'
                        : 'mdi-check-circle-outline'
                    "
                    color="primary"
                    :label="btr('At least 8 characters')"
                    :success="password.length >= 8"
                    hide-details
                  ></v-input>
                  <v-input
                    class="g-confirm"
                    :prepend-icon="
                      /\d/.test(password)
                        ? 'mdi-check-circle'
                        : 'mdi-check-circle-outline'
                    "
                    color="primary"
                    :label="btr('At least 1 number')"
                    :success="/\d/.test(password)"
                    hide-details
                  ></v-input>
                  <v-input
                    class="g-confirm"
                    :prepend-icon="
                      password.search(/[a-z]/) > -1 &&
                      password.search(/[A-Z]/) > -1
                        ? 'mdi-check-circle'
                        : 'mdi-check-circle-outline'
                    "
                    color="primary"
                    :label="btr('Uppercase and Lowercase')"
                    :success="
                      password.search(/[a-z]/) > -1 &&
                      password.search(/[A-Z]/) > -1
                    "
                    hide-details
                  ></v-input>
                  <div>
                    <v-checkbox
                      :error="termsError"
                      color="primary"
                      v-model="termsApproval"
                      value="1"
                      hide-details
                      :label="btr('I Accept Company Terms and conditions')"
                    ></v-checkbox>
                  </div>
                </div>
                <v-btn
                  block
                  depressed
                  large
                  :disabled="!passwordOk"
                  :loading="loading"
                  color="#40929d"
                  class="white--text mt-5 font-weight-regular"
                  @click="checkSignup(3)"
                  >{{ btr("Avanti") }}</v-btn
                >
                <v-checkbox
                  color="primary"
                  :label="btr('Remember me')"
                  v-model="rememberMe"
                  hide-details
                ></v-checkbox>
              </v-stepper-content>
              <v-stepper-content step="3">
                <div>
                  <v-text-field
                    ref="emailInput"
                    class="mt-1"
                    outlined
                    autocomplete="nope"
                    v-model="first_name"
                    :rules="[(v) => !!v || btr('Please fill this field')]"
                    label="Nome"
                    required
                  ></v-text-field>
                  <v-text-field
                    ref="emailInput"
                    class="mt-1"
                    outlined
                    autocomplete="nope"
                    v-model="last_name"
                    :rules="[(v) => !!v || btr('Please fill this field')]"
                    label="Cognome"
                    required
                  ></v-text-field>
                  <v-autocomplete
                    autocomplete="nope"
                    :items="countryCodes"
                    item-text="name"
                    item-value="code"
                    label="Country"
                    outlined
                    v-model="country"
                  ></v-autocomplete>
                  <v-row>
                    <v-col xs="1" md="6">
                      <v-autocomplete
                        :items="countryCodes"
                        autocomplete="nope"
                        item-text="name"
                        item-value="dial_code"
                        label="Country Code"
                        outlined
                        v-model="telephoneCountry"
                      >
                        <template v-slot:selection="{ item }">{{
                          item.dial_code
                        }}</template>
                      </v-autocomplete>
                    </v-col>
                    <v-col xs="3" md="6">
                      <v-text-field
                        ref="emailInput"
                        outlined
                        autocomplete="nope"
                        v-model="telephone"
                        :rules="[(v) => !!v || btr('Please fill this field')]"
                        label="Telefono"
                        required
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </div>
                <v-btn
                  block
                  depressed
                  large
                  :disabled="!first_name || !last_name || !country"
                  :loading="loading"
                  color="#40929d"
                  class="white--text mt-5 font-weight-regular"
                  @click="checkSignup(4)"
                  >{{ btr("Crea il tuo account Guestki") }}</v-btn
                >
              </v-stepper-content>
              <v-stepper-content step="8">
                <div class="my-6 text-center" v-if="!listing">
                  <h3 class="display-1 font-weight-bold my-5">
                    Aggiungi Annuncio
                  </h3>
                  <p>
                    Da qui potrai aggiungere l’annuncio della tua proprietà o
                    struttura turistica
                  </p>
                </div>
                <div>
                  <div v-if="!listing_name">
                    <v-radio-group
                      v-model="propertyInsertMode"
                      @change="
                        propertyInsertDisabled =
                          propertyInsertMode != 'manualInsert'
                      "
                    >
                      <v-radio
                        color="primary"
                        label="Importa da AirBnB"
                        value="airbnbImport"
                      ></v-radio>
                      <v-radio
                        color="primary"
                        label="Inserisci manualmente"
                        value="manualInsert"
                      ></v-radio>
                    </v-radio-group>
                    <!-- <v-select
                    v-model="propertyInsertMode"
                    :items="[{'text' : 'Importa da AirBnB', 'value' : 'airbnbImport'}, {'text' : 'Inserisci manualmente', 'value' : 'manualInsert'}]"
                    label="Modalità inserimento annuncio"
                    outlined
                    @change="propertyInsertDisabled = propertyInsertMode != 'manualInsert'"
                    ></v-select>-->
                    <div v-if="propertyInsertMode == 'airbnbImport'">
                      <v-alert color="warning" class="white--text">
                        {{
                          btr("Incolla il link pubblico della tua struttura")
                        }}
                        <v-divider class="my-2"></v-divider>
                        <v-icon small class="mr-2">info</v-icon>
                        <a href="#" class="white--text caption"
                          >Dove trovo il link della struttura?</a
                        >
                      </v-alert>
                      <v-text-field
                        :loading="loading"
                        v-model="airbnbLink"
                        :rules="[(v) => !!v || btr('Please fill this field')]"
                        label="link"
                        required
                        type="link"
                        prepend-icon="link"
                      ></v-text-field>
                      <v-divider class="my-4"></v-divider>
                    </div>
                  </div>

                  <div v-if="propertyInsertMode != null">
                    <v-text-field
                      :loading="loading"
                      class="mt-1"
                      :outlined="!propertyInsertDisabled"
                      :disabled="propertyInsertDisabled"
                      :filled="propertyInsertDisabled"
                      v-model="listing_name"
                      :rules="[(v) => !!v || btr('Please fill this field')]"
                      label="Nome Proprietà"
                      required
                      prepend-icon="house"
                    ></v-text-field>
                    <v-text-field
                      :loading="loading"
                      class="mt-1"
                      :disabled="!listing_name"
                      :filled="!listing_name"
                      :outlined="!!listing_name"
                      v-model="listing_nickname"
                      :rules="[(v) => !!v || btr('Please fill this field')]"
                      label="Soprannome Proprietà"
                      :placeholder="listing_nickname_help"
                      required
                      prepend-icon="favorite"
                    ></v-text-field>
                    <v-text-field
                      class="mt-1"
                      :disabled="!listing_name"
                      :filled="!listing_name"
                      :outlined="!!listing_name"
                      v-model="listing_city"
                      :rules="[(v) => !!v || btr('Please fill this field')]"
                      label="Città"
                      required
                      prepend-icon="room"
                    ></v-text-field>
                    <v-text-field
                      class="mt-1"
                      :disabled="!listing_name"
                      :filled="!listing_name"
                      :outlined="!!listing_name"
                      v-model="listing_address"
                      :rules="[(v) => !!v || btr('Please fill this field')]"
                      label="Indirizzo"
                      required
                      prepend-icon="gps_fixed"
                    ></v-text-field>
                    <v-autocomplete
                      :items="countryCodes"
                      item-text="name"
                      item-value="code"
                      label="Country"
                      outlined
                      autocomplete="nope"
                      v-model="listing_country_code"
                      prepend-icon="map"
                    ></v-autocomplete>
                    <v-row class="time-input">
                      <v-col xs="2" md="6">
                        <v-text-field
                          type="time"
                          outlined
                          v-model="listing_checkin_time"
                          :rules="[(v) => !!v || btr('Please fill this field')]"
                          label="Check-in"
                          required
                        ></v-text-field>
                      </v-col>
                      <v-col xs="2" md="6">
                        <v-text-field
                          type="time"
                          outlined
                          v-model="listing_checkout_time"
                          :rules="[(v) => !!v || btr('Please fill this field')]"
                          label="Check-out"
                          required
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col xs="3" md="6" style="line-height: 34px">
                        <strong>{{ btr("Adulti") }}</strong>
                      </v-col>
                      <v-col>
                        <v-btn
                          class="mx-2"
                          fab
                          x-small
                          outlined
                          color="#40929d"
                          @click="adults = adults - 1 < 1 ? 1 : adults - 1"
                        >
                          <v-icon dark>remove</v-icon>
                        </v-btn>

                        <strong>{{ adults }}</strong>

                        <v-btn
                          class="mx-2"
                          fab
                          x-small
                          outlined
                          color="#40929d"
                          @click="adults++"
                        >
                          <v-icon dark>add</v-icon>
                        </v-btn>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col xs="3" md="6" style="line-height: 34px">
                        <strong>{{ btr("Bambini") }}</strong>
                      </v-col>
                      <v-col>
                        <v-btn
                          class="mx-2"
                          fab
                          x-small
                          outlined
                          color="#40929d"
                          @click="babies = babies - 1 < 0 ? 0 : babies - 1"
                        >
                          <v-icon dark>remove</v-icon>
                        </v-btn>

                        <strong>{{ babies }}</strong>

                        <v-btn
                          class="mx-2"
                          fab
                          x-small
                          outlined
                          color="#40929d"
                          @click="babies++"
                        >
                          <v-icon dark>add</v-icon>
                        </v-btn>
                      </v-col>
                    </v-row>
                    <!-- <v-combobox
                    class="mt-4"
                    v-model="listing_tags"
                    :items="[]"
                    :search-input.sync="search"
                    hide-selected
                    hint="Aggiungi dei tag per raggruppare le strutture"
                    label="Tag"
                    multiple
                    persistent-hint
                    small-chips
                    outlined
                  >
                    <template v-slot:no-data>
                      <v-list-item>
                        <v-list-item-content>
                          <v-list-item-title>
                            Premi
                            <kbd>enter</kbd> per aggiungere
                            <strong>{{ search }}</strong>
                          </v-list-item-title>
                        </v-list-item-content>
                      </v-list-item>
                    </template>
                    </v-combobox>-->
                    <div v-if="items">
                      <v-divider class="mt-2 mb-4"></v-divider>
                      <h3 class="subtitle-1 font-weight-bold">
                        Vuoi usare una di queste foto profilo?
                      </h3>
                      <v-radio-group
                        v-model="airbnb_user"
                        class="fullsizer mb-1"
                      >
                        <v-list three-line>
                          <template v-for="(item, index) in items">
                            <v-subheader
                              v-if="item.header"
                              :key="item.header"
                              v-text="item.header"
                            ></v-subheader>

                            <v-divider
                              v-else-if="item.divider"
                              :key="index"
                              :inset="item.inset"
                            ></v-divider>

                            <v-list-item v-else :key="item.title">
                              <v-list-item-action>
                                <v-radio
                                  color="primary"
                                  :value="item"
                                ></v-radio>
                              </v-list-item-action>
                              <v-list-item-avatar>
                                <v-img :src="item.avatar"></v-img>
                              </v-list-item-avatar>

                              <v-list-item-content>
                                <v-list-item-title
                                  v-html="item.title"
                                ></v-list-item-title>
                                <v-list-item-subtitle
                                  v-html="item.subtitle"
                                ></v-list-item-subtitle>
                              </v-list-item-content>
                            </v-list-item>
                          </template>
                        </v-list>
                      </v-radio-group>
                    </div>
                  </div>
                </div>
                <v-btn
                  block
                  depressed
                  large
                  :disabled="
                    !listing_name || !listing_address || !listing_country_code
                  "
                  :loading="loading"
                  color="primary"
                  class="white--text mt-5 font-weight-regular"
                  @click="checkSignup(4)"
                  >{{ btr("Inserisci annuncio") }}</v-btn
                >
                <v-btn
                  block
                  depressed
                  large
                  v-if="
                    propertyInsertMode != 'airbnbImport' &&
                    !propertyInsertDisabled
                  "
                  :loading="loading"
                  color="secondary"
                  class="white--text mt-5 font-weight-regular"
                  @click="
                    propertyInsertMode = 'airbnbImport';
                    $vuetify.goTo(0);
                    listing_name = null;
                    propertyInsertDisabled = true;
                  "
                  >{{ btr("Importa tramite AirBnB") }}</v-btn
                >
              </v-stepper-content>
              <v-stepper-content step="4">
                <div class="text-center mb-5">
                  <img src="@/assets/img/congrats.jpg" height="280" />
                  <h2 class="display-1 font-weight-bold my-5">
                    Iscrizione completata!
                  </h2>
                  <p>
                    <strong>
                      Benvenuto
                      <span style="color: #40929d">{{
                        first_name
                      }}</span> </strong
                    >! <br />Riceverai a breve un’email con la conferma della
                    tua registrazione. Da adesso puoi aggiungere annunci,
                    prenotazioni e dispositivi dal tuo pannello di controllo
                  </p>
                </div>
                <v-btn
                  block
                  depressed
                  large
                  color="#40929d"
                  class="white--text mt-5 font-weight-regular"
                  to="/listings"
                  >{{ btr("Vai ai tuoi annunci") }}</v-btn
                >
                <v-btn
                  block
                  depressed
                  large
                  text
                  color="#40929d"
                  class="grey--text mt-5 font-weight-regular"
                  @click="checkSignup()"
                  >{{ btr("impostazioni abbonamento") }}</v-btn
                >
              </v-stepper-content>
              <v-stepper-content step="6">
                <v-alert type="info" color="primary">{{
                  btr("Please paste your listing public link here")
                }}</v-alert>
                <v-text-field
                  v-model="airbnbLink"
                  :rules="[(v) => !!v || btr('Please fill this field')]"
                  label="link"
                  required
                  type="link"
                  prepend-icon="link"
                ></v-text-field>
                <div class="mt-8">
                  <v-btn
                    v-if="!airbnbLink"
                    color="warning"
                    class="white--text"
                    @click="e6 = e6 + 1"
                    >{{ btr("Skip") }}</v-btn
                  >
                  <v-btn
                    :loading="loading"
                    v-if="airbnbLink"
                    color="primary"
                    class="white--text"
                    @click="parseAirBnBLink()"
                    >{{ btr("Next") }}</v-btn
                  >
                  <v-btn text @click="e6 = 1">{{ btr("Previous") }}</v-btn>
                </div>
              </v-stepper-content>
              <v-stepper-content step="5">
                <v-list three-line>
                  <template v-for="(item, index) in items">
                    <v-subheader
                      v-if="item.header"
                      :key="item.header"
                      v-text="item.header"
                    ></v-subheader>

                    <v-divider
                      v-else-if="item.divider"
                      :key="index"
                      :inset="item.inset"
                    ></v-divider>

                    <v-list-item v-else :key="item.title">
                      <v-list-item-avatar>
                        <v-img :src="item.avatar"></v-img>
                      </v-list-item-avatar>

                      <v-list-item-content>
                        <v-list-item-title
                          v-html="item.title"
                        ></v-list-item-title>
                        <v-list-item-subtitle
                          v-html="item.subtitle"
                        ></v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                  </template>
                </v-list>
                <div class="mt-8">
                  <v-btn
                    v-if="!airbnbLink"
                    color="warning"
                    class="white--text"
                    @click="e6 = e6 + 1"
                    >{{ btr("Skip") }}</v-btn
                  >
                  <v-btn
                    color="primary"
                    class="white--text"
                    @click="parseAirBnBLink()"
                    >{{ btr("Next") }}</v-btn
                  >
                  <v-btn text @click="e6 = 1">{{ btr("Previous") }}</v-btn>
                </div>
              </v-stepper-content>
            </v-stepper-items>
          </v-form>

          <div class="additional px-6 pb-5" v-if="e6 < 4">
            <div class="text-center caption">
              <v-divider class="my-5"></v-divider>Hai già un account?
              <a href="#">Accedi</a>
              <v-divider class="my-5"></v-divider>
            </div>
            <div class="caption">
              Se desideri ulteriori informazioni su come Guestki raccoglie,
              elabora, condivide e protegge i tuoi dati personali, leggi
              <a href="#">Informativa sulla privacy</a> di Guestki.
            </div>
          </div>
        </v-stepper>
      </v-container>
    </div>
  </div>
</template>
<script>
import axios from "axios";

export default {
  components: {},
  data() {
    return {
      adults: 1,
      babies: 0,
      coverBg: null,
      headerContent: null,
      listing: null,
      listing_name: null,
      listing_nickname: null,
      listing_nickname_help: null,
      listing_city: null,
      listing_address: null,
      listing_country_code: null,
      listing_checkin_time: null,
      listing_checkout_time: null,
      listing_tags: null,
      propertyInsertMode: null,
      propertyInsertDisabled: true,
      airbnb_user: null,
      error: false,
      termsError: false,
      errorMessage: "",
      minLenght: false,
      e6: 1,
      password: "",
      passwordOk: false,
      isPassword: String,
      email: null,
      first_name: null,
      last_name: null,
      telephone: null,
      telephoneCountry: null,
      country: null,
      marketingApproval: false,
      termsApproval: false,
      rememberMe: true,
      airbnbLink: null,
      loading: false,
      countryCodes: [],
      items: [],
    };
  },
  mounted() {
    this.$store.state.hideTopbar = true;
    if (this.e6 == 3 || this.e6 == 8) {
      this.getCountryCodes();
    }
  },
  watch: {
    termsApproval(t) {
      if (t) {
        this.termsError = false;
      }
    },
    airbnbLink(l) {
      if ((l.includes("airbnb") || l.includes("abnb.me")) && l.length > 10) {
        this.parseAirBnBLink();
        this.$vuetify.goTo(0);
      }
    },
    listing(l) {
      if (l) {
        console.log(l);
        this.listing_name = l.name;
        this.headerContent = l.name;
        this.listing_country_code = l.country_code;
        this.listing_city = l.city;
        this.listing_checkin_time = l.check_in_time + ":00";
        this.listing_checkout_time = l.check_out_time + ":00";
        this.coverBg =
          "background: url('" + l.xl_picture_url + "') center / cover;";
      }
    },
    listing_name(ln) {
      if (ln && ln.length > 9 && !this.listing_nickname_help) {
        var nam = ln.split(" ");
        this.listing_nickname_help = nam[0];
        if (this.listing_nickname_help.length < 5) {
          this.listing_nickname_help =
            this.listing_nickname_help + " " + nam[1];
        }
        this.listing_nickname_help = "Es. " + this.listing_nickname_help;
        if (this.listing) {
          this.listing_nickname_help +=
            ", Casa di " + this.listing.primary_host.first_name;
        }
      }
    },
    password(p) {
      if (
        p.length >= 8 &&
        /\d/.test(p) &&
        p.search(/[a-z]/) > -1 &&
        p.search(/[A-Z]/) > -1
      ) {
        this.passwordOk = true;
      } else {
        this.passwordOk = false;
      }
      //var hasNumber = /\d/.test(myString);
    },
  },
  computed: {
    isDev: function () {
      return top.location.href.includes("http://localhost");
    },
  },
  methods: {
    getBaseUrl() {
      return "http://bumip.test/";
      if (top.location.href.includes("http://localhost")) {
        return "http://localhost:8080/hosting2/";
      }
      if (top.location.href.includes("http://192.168.1.100")) {
        return "http://192.168.1.100:8080/hosting2/";
      }
      return "https://app.tomokit.com/hosting2/";
    },
    getCountryCodes() {
      var countryurl = this.getBaseUrl() + "countries.json";
      axios.get(countryurl).then((res) => {
        this.countryCodes = res.data;
      });
    },
    checkSignup(step) {
      this.loading = true;
      if (this.passwordOk && !this.termsApproval) {
        this.termsError = true;
        this.loading = false;
        return false;
      } else if (step == 3) {
        this.loading = false;
        this.e6 = 3;
        this.getCountryCodes();
        return true;
      } else if (step == 4) {
        this.loading = false;
        this.e6 = 4;
        this.$vuetify.goTo(0);
        return true;
      }
      this.termsError = false;
      if (this.email) {
        axios
          .post(
            "https://app.tomokit.com/signup",
            this.toFormData({ email: this.email })
          )
          .then((res) => {
            if (res.data.error) {
              this.errorMessage = this.btr("this is not a valid email");
              this.error = true;
              this.loading = false;
              return false;
            }
            if (!res.data.exists) {
              this.loading = false;
              this.error = false;
              this.e6 = 2;
            } else {
              this.error = true;
              this.loading = false;
              this.errorMessage = this.btr("Questa email è già registrata");
            }
          })
          .catch((err) => console.log(err));
      }
    },
    parseAirBnBLink() {
      this.loading = true;
      if (this.airbnbLink) {
        this.items = [];
        axios
          .post(
            "https://app.tomokit.com/getAirBnBData",
            this.toFormData({ link: this.airbnbLink })
          )
          .then((res) => {
            if (res.data.listing.hosts) {
              this.listing = res.data.listing;
              var i = 0;
              while (res.data.listing.hosts[i]) {
                var u = res.data.listing.hosts[i];
                console.log(u.first_name);
                var subtitle = "co-host";
                if (this.listing.primary_host.first_name == u.first_name) {
                  subtitle = "host principale";
                }
                this.items.push({
                  avatar: u.thumbnail_url,
                  title: u.first_name,
                  subtitle: subtitle,
                });
                this.items.push({ divider: true, inset: true });
                i++;
              }
              //this.e6 = 3;
              this.loading = false;
              console.log(this.e6);
            }
          })
          .catch((err) => console.log(err));
      }
    },
  },
};
</script>