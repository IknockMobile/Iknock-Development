import React from 'react';
import { TouchableOpacity, Image, Animated } from "react-native";
import { Item, Label, Input, InputGroup } from 'native-base';
import colors from '../assets/colors';
const InputField = (props) => {
    return (

        <Item stackedLabel last>
            <Label style={{ color: colors.YellowTextColor }}>{props.label}</Label>
            <InputGroup iconRight>
                <Input placeholder={props.placeholder} style={{ color: colors.White, fontSize: 18 }}
                    placeholderTextColor={colors.White}
                    secureTextEntry={props.secureTextEntry}
                    value={props.value}
                    keyboardType={props.keyboardType}
                    onChangeText={props.onChangeText} />


                <TouchableOpacity onPress={props.iconPressed} style={{ alignItems: 'center', justifyContent: 'center', marginRight: 15 }} >
                    <Animated.Image resizeMode='contain' source={props.iconImg} style={{ width: 24, height: 24 }} />
                </TouchableOpacity>
            </InputGroup>

        </Item>
    );
}
export { InputField };