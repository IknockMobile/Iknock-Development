import {
    LOGIN_USER_SUCCESS,
    LOGIN_USER_FAIL,
    IS__LOGIN_LOADING,

    RESET_PASSWORD_SUCCESS,
    RESET_PASSWORD_FAIL

} from '../../actions/types';

const initialState = {
    email: '',
    password: '',
    user: null,
    error: '',
    loading: false,
    userToken: '',
    message: ''
}

export default (state = initialState, action) => {
    switch (action.type) {
        case IS__LOGIN_LOADING:
            return { ...state, loading: true, error: '', message: '' }
        case LOGIN_USER_SUCCESS:
            return { ...state, ...initialState, user: action.payload.data, loading: false }
        case LOGIN_USER_FAIL:
            return { ...state, error: action.payload, loading: false, password: '' }

        case RESET_PASSWORD_SUCCESS:
            return { ...state, ...initialState, message: action.payload.message, loading: false }
        case RESET_PASSWORD_FAIL:
            return { ...state, error: action.payload, loading: false, password: '' }

        default:
            return state
    }
}