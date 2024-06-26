import React from 'react';
import { StyleSheet, Image, TouchableWithoutFeedback } from 'react-native';
import { Button, Text, ListItem, Item, Icon, Right, View } from 'native-base';
import colors from '../assets/colors';
import Images from '../assets';

const SquareButtonWithIcon = (props) => {
    return (
        <TouchableWithoutFeedback onPress={props.onPress}>
            <View style={[styles.btnStyle,{ backgroundColor: props.backgroundColor}]} >

                <Text style={{ color: '#fff', marginLeft: 10 }}>{props.title}</Text>

                <View style={{ position: 'absolute', right: 0, marginEnd: 10 }}>
                    <Image source={Images.forword_arrow} style={{ width: 25, height: 25 }} />
                </View>

            </View>
        </TouchableWithoutFeedback>
    );
}
const styles = StyleSheet.create({
    btnStyle: {
        height: 45,
        flex: 1,

        justifyContent: 'center',
        //alignItems: 'center',
        borderRadius: 3,
       
    }
});

export { SquareButtonWithIcon };