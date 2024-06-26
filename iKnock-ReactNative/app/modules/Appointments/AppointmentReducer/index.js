import {
  GET_MY_APPOINTMENT,
  GET_MY_APPOINTMENT_FAIL,
  GET_MY_APPOINTMENT_SUCCESS,
  MY_APPOINTMENT_REFRESHING,
  MY_APPOINTMENT_LOADING,
  GET_MY_APPOINTMENT_EXECUTION_SUCCESS,
  GET_MY_APPOINTMENT_EXECUTION_FAIL,
  EMAIL_DATE_TIME_CHANGED,
  PHONE_DATE_TIME_CHANGED,
  GET_FOLLOW_UP_SUCCESS,
  GET_FOLLOW_UP_FAIL,
  GET_MAIL_TEMPLETE_SUCCESS,
  GET_MAIL_TEMPLETE_FAIL,
  GET_TODAY_APPOINTMENT,
  GET_TODAY_APPOINTMENT_SUCCESS,
  GET_TODAY_APPOINTMENT_FAIL,
  SET_APPOINTMENT_SUCCESS,
  SET_APPOINTMENT_FAIL,
  GET_MONTHLY_APPOINTMENT,
  GET_MONTHLY_APPOINTMENT_SUCCESS,
  GET_MONTHLY_APPOINTMENT_FAIL,
} from '../../../actions/types';
import _ from 'lodash';
const initialState = {
  myAppointmentList: [],
  todayAppointmentList: [],
  monthlyAppointmentList: [],

  error: '',
  refreshing: false,
  loading: false,
  nextPage: null,
  currentPage: null,
  message: '',
  resultTypeText: '',
  mailTemplete: [],

  selectedMailDateTime: 'Select Date and Time',
  selectedPhoneDateTime: 'Select Date and Time',
};
export default (state = initialState, action) => {
  switch (action.type) {
    case MY_APPOINTMENT_REFRESHING:
      return {
        ...state,
        refreshing: true,
        loading: false,
        message: '',
        error: '',
      };
    case MY_APPOINTMENT_LOADING:
      return {
        ...state,
        refreshing: false,
        loading: true,
        message: '',
        error: '',
        todayAppointmentList: [],
      };

    case GET_MY_APPOINTMENT:
      return {
        ...state,
        myAppointmentList: [...state.myAppointmentList, ...action.payload.data],
        error: '',
        currentPage: action.payload?.meta?.current_page,
        nextPage: action.payload?.meta?.last_page,
        loading: false,
        refreshing: false,
      };
    case GET_MY_APPOINTMENT_SUCCESS:
      return {
        ...state,
        myAppointmentList: action.payload.data,
        error: '',
        nextPage: action.payload.meta.last_page,
        refreshing: false,
        loading: false,
        currentPage: action.payload?.meta?.current_page,
      };
    case GET_MY_APPOINTMENT_FAIL:
      return {
        ...state,
        error: action.payload,
        currentPage: action.payload?.meta?.current_page,
        nextPage: action.payload?.meta?.last_page,
        loading: false,
        refreshing: false,
      };

    case GET_TODAY_APPOINTMENT:
      return {
        ...state,
        todayAppointmentList: [
          ...state.todayAppointmentList,
          ...action.payload.data,
        ],
        error: '',
        currentPage: action.payload?.meta?.current_page,
        nextPage: action.payload?.meta?.last_page,
        loading: false,
        refreshing: false,
      };
    case GET_TODAY_APPOINTMENT_SUCCESS:
      return {
        ...state,
        todayAppointmentList: action.payload.data,
        error: '',
        nextPage: action.payload?.meta?.last_page,
        refreshing: false,
        loading: false,
        currentPage: action.payload?.meta?.current_page,
      };
    case GET_TODAY_APPOINTMENT_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
      };

    case GET_MONTHLY_APPOINTMENT:
      return {
        ...state,
        monthlyAppointmentList: [
          ...state.monthlyAppointmentList,
          ...action.payload.data,
        ],
        error: '',
        currentPage: action.payload?.meta?.current_page,
        nextPage: action.payload?.meta?.last_page,
        loading: false,
        refreshing: false,
      };
    case GET_MONTHLY_APPOINTMENT_SUCCESS:
      return {
        ...state,
        monthlyAppointmentList: action.payload.data,
        error: '',
        nextPage: action.payload?.meta?.last_page,
        refreshing: false,
        loading: false,
        currentPage: action.payload?.meta?.current_page,
        todayAppointmentList: [],
      };
    case GET_MONTHLY_APPOINTMENT_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
      };

    case GET_MY_APPOINTMENT_EXECUTION_SUCCESS:
      return {
        ...state,
        message: action.payload.message,
        loading: false,
        resultTypeText: '',
      };
    case GET_MY_APPOINTMENT_EXECUTION_FAIL:
      return {...state, error: action.payload, loading: false, message: ''};

    case EMAIL_DATE_TIME_CHANGED:
      return {
        ...state,
        selectedMailDateTime: action.payload,
        error: '',
        message: '',
      };
    case PHONE_DATE_TIME_CHANGED:
      return {
        ...state,
        selectedPhoneDateTime: action.payload,
        error: '',
        message: '',
      };

    case GET_FOLLOW_UP_SUCCESS:
      return {...state, message: action.payload.message, loading: false};
    case GET_FOLLOW_UP_FAIL:
      return {...state, error: action.payload, loading: false, message: ''};

    case GET_MAIL_TEMPLETE_SUCCESS:
      return {
        ...state,
        mailTemplete: action.payload.data,
        error: '',
        refreshing: false,
        loading: false,
      };
    case GET_MAIL_TEMPLETE_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
      };

    case SET_APPOINTMENT_SUCCESS:
      return {...state, message: action.payload.message, loading: false};
    case SET_APPOINTMENT_FAIL:
      return {...state, error: action.payload, loading: false, message: ''};

    case 'ON_LEAD_APPOINTMENT_UPDATE':
      let index = action.index;
      const temp_data = _.cloneDeep(state.myAppointmentList);

      temp_data[index].appointment_result = action.payload.appointment_result;

      return {...state, myAppointmentList: temp_data};

    default:
      return state;
  }
};
