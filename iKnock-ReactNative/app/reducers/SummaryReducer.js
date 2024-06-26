import {
  GET_STATUS_FAIL,
  GET_STATUS_SUCCESS,
  IS_LOADING,
  IS_REFRESHING,
  GET_LEAD_DETAIL_SUCCESS,
  GET_LEAD_DETAIL_FAIL,
  CHANGE_VALUE_QUERY_SUMMARY,
  CHANGE_VALUE_QUERY_APPOINTMENT,
  GET_TENANT_USER,
  GET_TENANT_USER_SUCCESS,
  GET_TENANT_USER_FAIL,
  USER_ASSIGN_LEAD_SUCCESS,
  USER_ASSIGN_LEAD_FAIL,
  ADD_SUMMARY_QUERY_SUCCESS,
  ADD_SUMMARY_QUERY_FAIL,
  CHANGE_DATE_TIME,
  ADD_APPOINTMENT_QUERY_SUCCESS,
  ADD_APPOINTMENT_QUERY_FAIL,
  CHANGE_LEAD_STATUS_SUCCESS,
  CHANGE_LEAD_STATUS_FAIL,
  GET_TYPE_LIST_SUCCESS,
  GET_TYPE_LIST_FAIL,
  CHANGE_LEAD_IMAGE_SUCCESS,
  CHANGE_LEAD_IMAGE_FAIL,
} from '../actions/types';

const InitialState = {
  stateList: [],
  leadDetail: {},
  querySummary: [],
  queryAppointment: [],
  error: '',
  loading: false,
  refreshing: false,
  tenantUserList: [],
  leadTypeList: [],
  message: '',
  dateTime: '',
  nextPage: null,
  currentPage: null,
  type: '',
  isEditable: false,
};
export default (state = InitialState, action) => {
  switch (action.type) {
    case IS_REFRESHING:
      return {
        ...state,
        refreshing: true,
        loading: false,
        message: '',
        error: '',
        type: '',
        isEditable: false,
      };
    case IS_LOADING:
      return {
        ...state,
        loading: true,
        message: '',
        error: '',
        refreshing: false,
        type: '',
        isEditable: false,
      };
    case GET_STATUS_SUCCESS:
      return {
        ...state,
        stateList: action.payload.data,
        loading: false,
        error: '',
        message: '',
      };
    case GET_STATUS_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        error: '',
        message: '',
      };

    case GET_TENANT_USER:
      return {
        ...state,
        tenantUserList: [...state.tenantUserList, ...action.payload.data],
        error: '',
        currentPage: action.payload?.meta?.current_page,
        nextPage: action.payload?.meta?.last_page,
        loading: false,
        message: '',
        refreshing: false,
      };
    case GET_TENANT_USER_SUCCESS:
      return {
        ...state,
        tenantUserList: action.payload.data,
        loading: false,
        refreshing: false,
        message: '',
        nextPage: action.payload?.meta?.last_page,
        currentPage: action.payload?.meta?.current_page,
      };
    case GET_TENANT_USER_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
        message: '',
        currentPage: action.payload?.meta?.current_page,
        nextPage: action.payload?.meta?.last_page,
      };

    case GET_TYPE_LIST_SUCCESS:
      return {
        ...state,
        leadTypeList: action.payload.data,
        loading: false,
        refreshing: false,
        message: '',
      };
    case GET_TYPE_LIST_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
        message: '',
      };

    case GET_LEAD_DETAIL_SUCCESS:
      return {
        ...state,
        leadDetail: action.payload.data,
        querySummary: action.payload.data.query_summary,
        queryAppointment: action.payload.data.query_appointment,
        loading: false,
        message: '',
      };
    case GET_LEAD_DETAIL_FAIL:
      return {...state, error: action.payload, loading: false, message: ''};

    case CHANGE_VALUE_QUERY_SUMMARY:
      return {
        ...state,
        querySummary: state.querySummary.map(item =>
          item.query_id === action.id
            ? {...item, response: action.payload}
            : item,
        ),
        message: '',
        error: '',
        isEditable: true,
      };
    case CHANGE_VALUE_QUERY_APPOINTMENT:
      return {
        ...state,
        queryAppointment: state.queryAppointment.map(item =>
          item.query_id === action.id
            ? {...item, response: action.payload}
            : item,
        ),
        message: '',
        error: '',
        isEditable: true,
      };

    case USER_ASSIGN_LEAD_SUCCESS:
      return {
        ...state,
        leadDetail: action.payload.data,
        message: action.payload.message,
        loading: false,
        type: 'assign',
      };
    case USER_ASSIGN_LEAD_FAIL:
      return {...state, error: action.payload, loading: false, message: ''};

    case CHANGE_LEAD_STATUS_SUCCESS:
      return {
        ...state,
        leadDetail: action.payload.data,
        message: action.payload.message,
        loading: false,
        type: 'status',
      };
    case CHANGE_LEAD_STATUS_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        message: '',
        type: '',
      };

    case ADD_SUMMARY_QUERY_SUCCESS:
      return {
        ...state,
        leadDetail: action.payload.data,
        querySummary: state.querySummary.map(item => {
          item.response = '';
          return item;
        }),
        message: action.payload.message,
        loading: false,
        type: 'status',
      };
    case ADD_SUMMARY_QUERY_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        message: '',
        type: '',
      };

    case CHANGE_DATE_TIME:
      return {...state, dateTime: action.payload, error: '', message: ''};
    case ADD_APPOINTMENT_QUERY_SUCCESS:
      return {
        ...state,
        leadDetail: action.payload.data,
        message: action.payload.message,
        loading: false,
        dateTime: '',
      };
    case ADD_APPOINTMENT_QUERY_FAIL:
      return {...state, error: action.payload, loading: false};

    case CHANGE_LEAD_IMAGE_SUCCESS:
      return {
        ...state,
        message: action.payload.message,
        leadDetail: action.payload.data,
        loading: false,
      };
    case CHANGE_LEAD_IMAGE_FAIL:
      return {...state, error: action.payload, loading: false, message: ''};

    default:
      return state;
  }
};
