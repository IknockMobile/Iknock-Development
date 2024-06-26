import React from 'react';
import { Item, Label, Input } from 'native-base';
import colors from '../assets/colors';
const RoundInputField = (props) => {
    return (
        <Item regular style={{ marginLeft: 15, marginRight: 15, marginTop: 15, backgroundColor: colors.LightGrey }}>

            <Input placeholder={props.placeholder}
                multiline
                style={{ minHeight: 40 }}
            />
        </Item>
    );
}
export { RoundInputField };