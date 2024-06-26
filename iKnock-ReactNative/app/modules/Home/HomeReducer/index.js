import {
  GET_LEAD,
  GET_LEAD_SUCCESS,
  GET_LEAD_FAIL,
  GET_MAP_LEAD,
  GET_MAP_LEAD_SUCCESS,
  GET_MAP_LEAD_FAIL,
  GET_MAP_MY_LEAD,
  GET_MAP_MY_LEAD_SUCCESS,
  GET_MAP_MY_LEAD_FAIL,
  IS_LOADING_Lead,
  IS_REFRESHING_Lead,
  GET_MYLEAD,
  GET_MYLEAD_FAIL,
  GET_MYLEAD_SUCCESS,
} from '../../../actions/types';
import _ from 'lodash';

const initialState = {
  leadsList: [],
  mapLeadList: [],
  mapMyLeadList: [],

  myLeadList: [],
  error: '',
  refreshing: false,
  loading: false,
  nextPage: null,
  currentPage: null,
};
export default (state = initialState, action) => {
  switch (action.type) {
    case IS_REFRESHING_Lead:
      return {...state, refreshing: true, loading: false};
    case IS_LOADING_Lead:
      return {...state, refreshing: false, loading: true};
    case GET_LEAD:
      return {
        ...state,
        leadsList: [...state.leadsList, ...action.payload.data],
        error: '',
        currentPage: action.payload?.meta?.current_page,
        nextPage: action.payload.meta.last_page,
        loading: false,
        refreshing: false,
      };
    case GET_LEAD_SUCCESS:
      return {
        ...state,
        leadsList: action.payload.data,
        error: '',
        nextPage: action.payload?.meta?.last_page,
        refreshing: false,
        loading: false,
        currentPage: action.payload.meta?.current_page,
      };
    case GET_LEAD_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
      };

    case GET_MAP_LEAD: {
      return {
        ...state,
        mapLeadList: [...state.mapLeadList, ...action.payload.data],
        error: '',
        loading: false,
        refreshing: false,
      };
    }

    case GET_MAP_LEAD_SUCCESS:
      return {
        ...state,
        mapLeadList: action.payload.data,
        error: '',
        refreshing: false,
        loading: false,
      };
    case GET_MAP_LEAD_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
      };

    case GET_MAP_MY_LEAD:
      return {
        ...state,
        mapMyLeadList: [...state.mapMyLeadList, ...action.payload.data],
        error: '',
        loading: false,
        refreshing: false,
      };
    case GET_MAP_MY_LEAD_SUCCESS:
      return {
        ...state,
        mapMyLeadList: action.payload.data,
        error: '',
        refreshing: false,
        loading: false,
      };
    case GET_MAP_MY_LEAD_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
      };

    case GET_MYLEAD:
      return {
        ...state,
        myLeadList: [...state.myLeadList, ...action.payload.data],
        error: '',
        currentPage: action.payload?.meta?.current_page,
        nextPage: action.payload?.meta?.last_page,
        loading: false,
        refreshing: false,
      };
    case GET_MYLEAD_SUCCESS:
      return {
        ...state,
        myLeadList: action.payload.data,
        error: '',
        nextPage: action.payload.meta.last_page,
        loading: false,
        refreshing: false,
        currentPage: action.payload?.meta?.current_page,
      };
    case GET_MYLEAD_FAIL:
      return {
        ...state,
        error: action.payload,
        loading: false,
        refreshing: false,
      };

    case 'CLEAR_MAP_DATA':
      return {...state, mapLeadList: [], mapMyLeadList: []};

    case 'ON_LEAD_ITEM_UPDATE':
      if (action.lead_type === 'leadList') {
        let index = action.index;
        const temp_data = _.cloneDeep(state.leadsList);

        temp_data[index] = action.payload;

        return {...state, leadsList: temp_data};
      }

      if (action.lead_type === 'myLeadList') {
        let index = action.index;
        const temp_data = _.cloneDeep(state.myLeadList);

        temp_data[index] = action.payload;

        return {...state, myLeadList: temp_data};
      }

      if (action.lead_type === 'mapLeadList') {
        let index = action.index;

        const temp_data = _.cloneDeep(state.mapLeadList);
        temp_data[index] = action.payload;
        return {...state, mapLeadList: temp_data};
      }
      if (action.lead_type === 'myMapLeadList') {
        let index = action.index;

        const temp_data = _.cloneDeep(state.mapMyLeadList);
        temp_data[index] = action.payload;
        return {...state, mapMyLeadList: temp_data};
      }

    default:
      return state;
  }
};
