import React from 'react';
import {  View } from 'react-native';
import {  Text,Badge } from 'native-base';

const GraphIndicator = (props) => {
    return (
        <View style={{ flex: 1, flexDirection: 'row', }}>
           
            <Badge style={{ backgroundColor: props.backgroundColor, width: 15, height: 15 }}>
                {/* <Text>2</Text> */}
            </Badge>
            <Text note style={{marginStart:10}}>{props.title}</Text>
        </View>
    );
}

export { GraphIndicator };