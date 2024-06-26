import React from 'react';
import { StyleSheet, Image, TouchableWithoutFeedback } from 'react-native';
import { Button, Text, ListItem, Item, Icon, Right, View } from 'native-base';
import colors from '../assets/colors';
import Images from '../assets';

const SquareGeryButtonWithIcon = (props) => {
    return (
        <TouchableWithoutFeedback onPress={props.onPress}>
            <View style={[styles.btnStyle, { backgroundColor: props.backgroundColor }]} >

                <Text style={{ color: props.textColor }} uppercase={false}> {props.title} </Text>

                <View style={{ position: 'absolute', right: 0, marginEnd: 10 }}>
                    <Image source={props.selectedItemId === props.id ? Images.ic_checked : Images.ic_unchecked} style={{ width: 25, height: 25 }} />
                </View>

            </View>
        </TouchableWithoutFeedback>
    );
}
const styles = StyleSheet.create({
    btnStyle: {
        height: 45,
        justifyContent: 'center',
        alignItems: 'center',
        borderRadius: 3,
        margin: 8,
        backgroundColor: colors.btnDarkBlueBgColor
    }
});

export { SquareGeryButtonWithIcon };