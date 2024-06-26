import {
  GET_TRAINING_LIST,
  GET_TRAINING_LIST_SUCCESS,
  GET_TRAINING_LIST_FAIL,
  IS_LOADING,
  IS_REFRESHING,
} from '../actions/types';

const initialState = {
  trainerList: [],
  error: '',
  message: '',
  loading: false,
  refreshing: false,
  nextPage: null,
  currentPage: null,
};

export default (state = initialState, action) => {
  switch (action.type) {
    case IS_REFRESHING:
      return {...state, refreshing: true, loading: false, error: ''};
    case IS_LOADING:
      return {...state, refreshing: false, loading: true, error: ''};
    case GET_TRAINING_LIST:
      return {
        ...state,
        trainerList: [...trainerList, action.payload.data],
        loading: false,
        refreshing: false,
        nextPage: action.payload.meta.last_page,
        currentPage: action.payload?.meta?.current_page,
      };
    case GET_TRAINING_LIST_SUCCESS:
      return {
        ...state,
        trainerList: action.payload.data,
        error: '',
        loading: false,
        refreshing: false,
        nextPage: action.payload.meta.last_page,
        currentPage: action.payload?.meta?.current_page,
      };
    case GET_TRAINING_LIST_FAIL:
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
