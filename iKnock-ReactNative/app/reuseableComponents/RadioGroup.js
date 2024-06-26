import React from "react";
import RadioForm from 'react-native-simple-radio-button';

const RadioGroup = (props) => {
    return (
        <RadioForm
            radio_props={props.data_props}
            initial={-1}
            buttonColor={'#20295c'}
            selectedButtonColor={'#20295c'}
            onPress={props.onPress}
        >
        </RadioForm>
    )
}

export { RadioGroup }