import React from 'react';
import { Item, Input, Text, View } from "native-base";
import colors from '../assets/colors';

const InputFieldWithLabel = (props) => {

    let key = props.id;
    let query = props.query;
    let response = props.response;
    let index = props.index
    return (

        <View style={{ flexDirection: 'column', marginLeft: 15, marginRight: 15, marginTop: 10, }}>
            <Text>{query}</Text>
            <Item regular style={{ marginTop: 2, backgroundColor: colors.LightGrey }}>

                <Input
                    placeholder={''}
                    key={key}
                    value={response}
                    //onEndEditing={(e) => props.onEndEditing(e.nativeEvent.text, key, index)}
                    onChangeText={(text) => props.onChangeText(text)}
                    multiline
                    style={{ minHeight: 40 }}
                >
                </Input>
            </Item>
        </View>
    );
}
export { InputFieldWithLabel }