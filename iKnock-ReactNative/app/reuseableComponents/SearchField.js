import React from 'react';
import {View, Image, StyleSheet, TouchableOpacity} from 'react-native';
import {Item, Icon, Input, Button, Text} from 'native-base';
import Images from '../assets';
const SearchField = props => {
  return (
    <Item regular style={{marginLeft: 15, marginRight: 15, marginTop: 15}}>
      <Icon active name="search" />
      <Input
        value={props.value}
        placeholder="Search"
        onChangeText={props.onChangeText}
      />
      {props.isFilter && (
        // <Button transparent onPress={props.onPress}>
        //     <Image source={Images.ic_filters} style={styles.iconStyle}></Image>
        //     <Text>{props.filterCount}</Text>
        // </Button>
        <TouchableOpacity onPress={props.onPress}>
          <View>
            <Image source={Images.ic_filters} style={styles.iconStyle} />
            {props.filterCount !== 0 ? (
              <View
                style={{
                  backgroundColor: 'red',
                  borderRadius: 10,
                  height: 18,
                  width: 18,
                  position: 'absolute',
                  left: 10,
                  justifyContent: 'center',
                  alignItems: 'center',
                }}>
                <Text style={{color: 'white'}}>{props.filterCount}</Text>
              </View>
            ) : (
              <View />
            )}
          </View>
        </TouchableOpacity>
      )}
      {!props.isFilter && (
        <Button transparent onPress={props.onPressCross}>
          <Image
            source={Images.ic_cancel_music}
            style={styles.iconStyle}></Image>
        </Button>
      )}
    </Item>
  );
};
const styles = StyleSheet.create({
  iconStyle: {
    width: 20,
    height: 20,
    marginRight: 10,
    padding: 10,
  },
});
export {SearchField};
