export const isEmailValid = (email) => {
    let reg = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    if (reg.test(email) === false) {
        return false;
    }
    return true;
}
export const isValidPassword = (Pssseord) => {
    if (Pssseord === '') { 
        return false;
    }
    return true;
}