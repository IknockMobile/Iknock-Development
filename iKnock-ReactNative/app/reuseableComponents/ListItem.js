import React from 'react';
import {Image, TouchableOpacity, Dimensions} from 'react-native';
import {Button, Text, View} from 'native-base';
import Images from '../assets';
import styles from '../assets/styles';
import colors from '../assets/colors';
import {getDeviceWidth} from '../Utility';
import moment from 'moment';

const ListItems = (props) => {
  const {
    item,
    leadTypeTitle,
    appointmantDate,
    address,
    name,
    statusColorCode,
    typeCode,
    isRightButton,
    onPress,
  } = props;

  const {status, visit_knocks, last_see_at} = item;
  const diffHours = moment().diff(moment(last_see_at), 'hours');
  return (
    <TouchableOpacity onPress={onPress}>
      <View
        style={{
          flex: 1,
          flexDirection: 'row',
          padding: 16,
          alignItems: 'center',
          justifyContent: 'space-between',
        }}>
        <View style={{flexDirection: 'row', flex: 1}}>
          {/* <Image source={{ uri: props.thumbnail[0].path }} style={{ width: 20, height: 20 }} /> */}
          <Image
            source={Images.ic_home}
            style={[{tintColor: statusColorCode}, {width: 20, height: 20}]}
          />
          <View style={{flexDirection: 'column', marginLeft: 10}}>
            <Text style={{color: colors.DarkBlue}} numberOfLines={2}>
              {name}
            </Text>
            <Text
              note
              numberOfLines={2}
              style={{width: getDeviceWidth() - 140}}>
              {address}, {item.city} - {item.zip_code}
            </Text>
            <Text note>{appointmantDate}</Text>
            <Text style={{width: getDeviceWidth() - 130}}>
              {leadTypeTitle && (
                <Text>
                  <Text style={{fontWeight: 'bold'}}>{`LT:`}</Text>
                  <Text>{` ${leadTypeTitle} - `}</Text>
                </Text>
              )}
              {status && (
                <Text>
                  <Text style={{fontWeight: 'bold'}}>{`LS:`}</Text>
                  <Text>{` ${status.title}`}</Text>
                </Text>
              )}
            </Text>
            <View
              style={{flexDirection: 'row', justifyContent: 'space-between'}}>
              <Text># Visits {visit_knocks}</Text>
              {last_see_at ? (
                <Text
                  style={{
                    paddingHorizontal: 10,
                    backgroundColor:
                      diffHours > 36 ? 'rgb(198,218,180)' : 'red',
                      fontWeight: 'bold'
                  }}>
                  Last Visit {moment().diff(moment(last_see_at), 'hours')} Hours
                </Text>
              ) : null}
            </View>
          </View>
        </View>

        <View style={{flexDirection: 'row', marginLeft: 5}}>
          {isRightButton === 1 ? (
            <Button
              style={{
                height: 25,
                alignItems: 'center',
                marginRight: 10,
                backgroundColor: statusColorCode,
              }}>
              <Text style={{fontSize: 13}}>{typeCode}</Text>
            </Button>
          ) : (
            <Button transparent style={{height: 20, marginRight: 10}}></Button>
          )}
          <Image source={Images.forword_arrow_blue} style={styles.iconSize} />
        </View>
      </View>
    </TouchableOpacity>
  );
};

export {ListItems};
