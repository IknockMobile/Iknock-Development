import React from 'react';
import { StyleSheet, Image } from 'react-native';
import { ListItem, Left, Body, Right, Thumbnail, Button, Text, Icon, View } from 'native-base';
import Images from '../assets';
import { getDeviceWidth } from "../Utility";
import colors from '../assets/colors';

const ListItemStatusHistory = (props) => {

    return (

        <View style={{ flex: 1, flexDirection: 'row', padding: 20, alignItems: 'center' }}>
            <View style={{ flexDirection: 'row' }}>
                {/* <Image source={props.thumbnail} style={{ width: 20, height: 20 }} /> */}
                {/* <Image source={Images.ic_home} style={[{ tintColor: props.statusColorCode }, { width: 20, height: 20 }]} /> */}
                <View style={{ flexDirection: 'column', marginLeft: 10, marginRight: 60 }}>
                    <Text style={{ color: props?.latest_status?.color_code }}>{props?.latest_status?.title}</Text>
                    {/* <Text style={{ color: colors.DarkBlue }}>{props.name}</Text>
                    <Text note numberOfLines={2}>{props.address}</Text> */}
                    <Text note numberOfLines={2}>Updated By: {props.userName}</Text>
                    <Text note numberOfLines={1}>{props.date}</Text>
                    {!props.statusTitle.includes('status updated')?<View style={{ flexDirection: 'row' }}>
                        <Text note style={{ color:colors.DarkGery, width: getDeviceWidth() - 150 }}>{props.statusTitle.includes('status updated')?'':props.statusTitle}</Text>
                    </View>:null}
                </View>
            </View>
            <View style={{ flexDirection: 'column', position: 'absolute', right: 0, marginEnd: 10 }}>

                {
                    props.isRightButton === 1 ?
                        <Button style={{ height: 25, marginLeft: 10, backgroundColor: props.statusColorCode, alignSelf: 'flex-end',justifyContent:'center',alignItems:'center' }}>
                            <Text style={{ fontSize: 13, lineHeight:16 }}>{props.typeCode}</Text>
                        </Button> :
                        <Button transparent style={{ height: 25, marginLeft: 10 }}></Button>
                }
            </View>
        </View>
    );
}

export { ListItemStatusHistory };