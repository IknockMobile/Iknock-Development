import React from 'react';
import { View } from 'react-native';
import { Text } from 'native-base';

const TextView = (props) => {
    return (
        <View style={props.styles}>
            <Text
                style={{
                    textAlign: props.textAlign,
                    fontWeight: props.fontWeight,
                    color: props.color,
                    fontSize:props.fontSize
                }}>{props.title}</Text>


        </View >
    );
}
export { TextView };