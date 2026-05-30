export interface RegistrationFormData {
  // Service User
  first_name: string;
  middle_names: string;
  surname: string;
  dob: string; // ISO date string: YYYY-MM-DD
  gender: string;
  housing_status: string;
  address: string;
  hostel_id: number | null;
  postcode: string;
  contact_number: string;
  food_allergies?: boolean;

  // Next of Kin
  nok_name: string;
  nok_relationship: string;
  nok_address: string;
  nok_contact_number: string;

  // Doctor
  gp_name: string;
  gp_address: string;
  gp_contact_number: string;

  // Registration
  referral_date: string;
  service_user_signature_date: string;
  volunteer_signature_date: string;
}