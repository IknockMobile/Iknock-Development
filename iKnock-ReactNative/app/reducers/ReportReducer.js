import {
    GET_REPORT_DATA_SUCCESS,
    GET_REPORT_DATA_FAIL,
    REPORT_IS_LOADING,

    COMMISSION_REPORT_IS_LOADING,
    GET_COMMISSION_REPORT_DATA_SUCCESS,
    GET_COMMISSION_REPORT_DATA_FAIL
} from "../actions/types";

const initialState = {
    reportData: [],
    commissionData: [],
    error: '',
    is_loading: false,
    message: ''
}

export default (state = initialState, action) => {
    switch (action.type) {
        case REPORT_IS_LOADING:
            return { ...state, is_loading: true, error: '', message: '', reportData: [] }
        case GET_REPORT_DATA_SUCCESS:
            return { ...state, reportData: action.payload.data, error: '', is_loading: false }
        case GET_REPORT_DATA_FAIL:
            return { ...state, error: action.payload, is_loading: false }

        case COMMISSION_REPORT_IS_LOADING:
            return { ...state, is_loading: true, error: '', message: '', commissionData: [] }
        case GET_COMMISSION_REPORT_DATA_SUCCESS:
            return { ...state, commissionData: action.payload.data, error: '', is_loading: false }
        case GET_COMMISSION_REPORT_DATA_FAIL:
            return { ...state, error: action.payload, is_loading: false }
        default:
            return state
    }
}