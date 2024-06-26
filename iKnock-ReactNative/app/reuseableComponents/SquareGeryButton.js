import React from 'react';
import { StyleSheet } from 'react-native';
import { Button, Text, ListItem, Item,Icon, Right } from 'native-base';
import colors from '../assets/colors'

const SquareGeryButton = (props) => {
    return (
            <Button block style={[styles.btnStyle,{backgroundColor:props.backgroundColor}]} onPress={props.onPress} >
                {/* <Text style={{ color: props.textColor }} uppercase={false}> {props.title} </Text> */}
                <Text style={{ color:props.textColor }} uppercase={false}> {props.title} </Text>
                
            </Button>
    );
}
const styles = StyleSheet.create({
    btnStyle: {
        flex: 1,
        backgroundColor: colors.White,
    }
});

export { SquareGeryButton };