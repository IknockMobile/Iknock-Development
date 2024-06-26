import {
  GET_STATUS_HISTORY,
  GET_STATUS_HISTORY_FAIL,
  GET_STATUS_HISTORY_SUCCESS,
  STATUS_LOADING,
  STATUS_REFRESHING,
} from '../actions/types';

const initialState = {
  statusHistoryList: [],
  error: '',
  refreshing: false,
  loading: false,
  nextPage: null,
  currentPage: null,
};
export default (state = initialState, action) => {
  switch (action.type) {
    case STATUS_REFRESHING:
      return {...state, refreshing: true, loading: false};
    case STATUS_LOADING:
      return {...state, refreshing: false, loading: true};

    case GET_STATUS_HISTORY:
      return {
        ...state,
        statusHistoryList: [...state.statusHistoryList, ...action.payload.data],
        error: '',
        currentPage: action.payload.meta?.current_page,
        nextPage: action.payload.meta.last_page,
        loading: false,
        refreshing: false,
      };
    case GET_STATUS_HISTORY_SUCCESS:
      return {
        ...state,
        statusHistoryList: action.payload.data,
        error: '',
        nextPage: 1,
        refreshing: false,
        loading: false,
        currentPage: 1,
      };
    case GET_STATUS_HISTORY_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
      };

    default:
      return state;
  }
};
