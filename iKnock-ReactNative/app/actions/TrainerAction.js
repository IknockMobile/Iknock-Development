import {
    GET_TRAINING_LIST,
    GET_TRAINING_LIST_SUCCESS,
    GET_TRAINING_LIST_FAIL,
    IS_LOADING,
    IS_REFRESHING
} from "./types";

import HttpServiceManager from "../HttpServiceManager/HttpServiceManager";
import constant from "../HttpServiceManager/constant";

export const getTrainerList = (page, is_refreshing = false, query = '') => {

    let url = constant.userTrainingList + '?page=' + page;
    if (query !== '') {
        url = constant.userTrainingList + '?page=' + page + '&search=' + query;
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
                        type: GET_TRAINING_LIST_SUCCESS,
                        payload: response
                    });
                } else {
                    dispatch({
                        type: GET_TRAINING_LIST,
                        payload: response
                    });
                }
            }).catch((error) => {
                dispatch({
                    type: GET_TRAINING_LIST_FAIL,
                    payload: error
                });

            });
    }
}
