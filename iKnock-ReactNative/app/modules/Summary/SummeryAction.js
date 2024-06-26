import { Platform } from "react-native";
import {
    GET_STATUS_SUCCESS,
    GET_STATUS_FAIL,

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

} from "../../actions/types";
import constant from "../../HttpServiceManager/constant";
import HttpServiceManager from "../../HttpServiceManager/HttpServiceManager";

export const changedSummary = (text, id) => {
    return {
        type: CHANGE_VALUE_QUERY_SUMMARY,
        payload: text,
        id: id
    }
}

export const changedAppointment = (text, id) => {
    return {
        type: CHANGE_VALUE_QUERY_APPOINTMENT,
        payload: text,
        id: id
    }
}

export const changedDateTime = (dateTime) => {
    return {
        type: CHANGE_DATE_TIME,
        payload: dateTime,
    }
}

export const getLeadDetail = (id, is_loading = false, cbSuccess, cbFailure) => {
    let url = constant.leadDetail + id;

    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: IS_LOADING
            });
        }
        HttpServiceManager.getInstance().request(url, '', 'get').
            then((response) => {
                console.log('Lead Detail response', response)
                response.data.query_summary.map((item)=>{
                    item.response = '';
                    return item;
                })
                dispatch({
                    type: GET_LEAD_DETAIL_SUCCESS,
                    payload: response
                });
                cbSuccess(response)
            }).catch((error) => {
                dispatch({
                    type: GET_LEAD_DETAIL_FAIL,
                    payload: error
                });
                cbFailure(error)
            });
    }
}

export const getStatusList = (is_loading = false) => {
    let url = constant.statusList;

    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: IS_LOADING
            });
        }
        HttpServiceManager.getInstance().request(url, '', 'get').
            then((response) => {
                dispatch({
                    type: GET_STATUS_SUCCESS,
                    payload: response
                });
            }).catch((error) => {
                dispatch({
                    type: GET_STATUS_FAIL,
                    payload: error
                });
            });
    }
}

export const getTenantUserList = (page, is_refreshing = false, query = "") => {
    let url = constant.getTenantUserList + "?page=" + page
    if (query !== '') {
        url = constant.getTenantUserList + "?name=" + query + '&page=' + page;
    }
    return (dispatch) => {
        if (is_refreshing) {
            dispatch({
                type: IS_REFRESHING
            });
        }
        HttpServiceManager.getInstance().request(url, '', 'get').
            then((response) => {

                if (is_refreshing) {
                    dispatch({
                        type: GET_TENANT_USER_SUCCESS,
                        payload: response
                    });
                } else {
                    dispatch({
                        type: GET_TENANT_USER,
                        payload: response
                    });
                }

                // dispatch({
                //     type: GET_TENANT_USER_SUCCESS,
                //     payload: response
                // });
            }).catch((error) => {
                dispatch({
                    type: GET_TENANT_USER_FAIL,
                    payload: error
                });
            });
    }
}
export const getTypeList = (is_refreshing = false) => {
    let url = constant.getTypeList

    return (dispatch) => {
        if (is_refreshing) {
            dispatch({
                type: IS_REFRESHING
            });
        }
        HttpServiceManager.getInstance().request(url, '', 'get').
            then((response) => {
                dispatch({
                    type: GET_TYPE_LIST_SUCCESS,
                    payload: response
                });
            }).catch((error) => {
                dispatch({
                    type: GET_TYPE_LIST_FAIL,
                    payload: error
                });
            });
    }
}
export const UserAssignLead = (lead_id, target_id, is_loading = false, loginUserId) => {
    let url = constant.userAssignLead + "/" + lead_id;

    let params = {
        "target_id": target_id,
        user_login_id:loginUserId
    }

    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: IS_LOADING
            });
        }
        HttpServiceManager.getInstance().request(url, params, 'post').
            then((response) => {
                dispatch({
                    type: USER_ASSIGN_LEAD_SUCCESS,
                    payload: response
                });
            }).catch((error) => {
                dispatch({
                    type: USER_ASSIGN_LEAD_FAIL,
                    payload: error
                });
            });
    }
}
//update lead Status 
export const changeLeadStatus = (lead_id, status_id, is_loading = false) => {
    let url = constant.leadStatusUpdate;
    let params = {
        "lead_id": lead_id,
        "status_id": status_id,
    }
    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: IS_LOADING
            });
        }
        HttpServiceManager.getInstance().request(url, params, 'post').
            then((response) => {
                dispatch({
                    type: CHANGE_LEAD_STATUS_SUCCESS,
                    payload: response
                });
            }).catch((error) => {
                dispatch({
                    type: CHANGE_LEAD_STATUS_FAIL,
                    payload: error
                });
            });
    }
}
//submit lead queries
export const addSummaryQuery = (lead_id, params, is_loading = false) => {

    let url = constant.addLeadQuery + "/" + lead_id;
    // let params = {
    //     "status_id": status_id,
    //     "query": JSON.stringify(summary_query),
    //     // is_verified
    // }
    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: IS_LOADING
            });
        }
        HttpServiceManager.getInstance().request(url, params, 'post').
            then((response) => {
                console.log('Update query', response);
                dispatch({
                    type: ADD_SUMMARY_QUERY_SUCCESS,
                    payload: response
                });
            }).catch((error) => {
                console.log('Query Ke',error)
                dispatch({
                    type: ADD_SUMMARY_QUERY_FAIL,
                    payload: error
                });
            });
    }
}
//create appointment
export const addAppointmentQuery = (lead_id, appointment_date, appointment_query, is_loading = false, isUpdate=0,appointment_phone) => {
    let url = constant.userLeadAppointmentCreate;

    let params = {
        "lead_id": lead_id,
        "appointment_date": appointment_date,
        "query": JSON.stringify(appointment_query),
        "update_appointment":isUpdate,
        "appointment_phone":appointment_phone
    }


    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: IS_LOADING
            });
        }
        HttpServiceManager.getInstance().request(url, params, 'post').
            then((response) => {
                dispatch({
                    type: ADD_APPOINTMENT_QUERY_SUCCESS,
                    payload: response
                });  
            }).catch((error) => {
                dispatch({
                    type: ADD_APPOINTMENT_QUERY_FAIL,
                    payload: error
                });
            });
    }
}

//update lead image
export const updateLeadImage = (lead_id, media, is_loading = false) => {
    let url = constant.leadMedia + "/" + lead_id;

    const formData = new FormData();
    if (media.length > 0) {

        media.map((e) => {
            formData.append('image_url[]', { uri: Platform.OS==="ios"? 'file://' + e.path: e.path, name: new Date().getTime()+ e.path.split('/').pop(), type: 'image/jpg' });
        });
        // formData.append('image_url[]', { uri: media, name: 'profile.jpg', type: 'image/jpeg' });
    }

    return (dispatch) => {
        if (is_loading) {
            dispatch({
                type: IS_LOADING
            });
        }
        HttpServiceManager.getInstance().request(url, formData, 'post').
            then((response) => {
                dispatch({
                    type: CHANGE_LEAD_IMAGE_SUCCESS,
                    payload: response
                });
            }).catch((error) => {
                console.log('errror',error)
                dispatch({
                    type: CHANGE_LEAD_IMAGE_FAIL,
                    payload: error
                });
            });
    }
}