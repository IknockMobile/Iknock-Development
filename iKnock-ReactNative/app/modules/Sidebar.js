import React, {Component} from 'react';
import {
  Image,
  StyleSheet,
  FlatList,
  ImageBackground,
  Platform,
} from 'react-native';
import DeviceInfo from 'react-native-device-info';

import PropTypes from 'prop-types';
import Images from '../assets';
import {Container, Text, ListItem, View} from 'native-base';
import {clearAll} from '../UserPreference';
import {clearMapData} from '../actions';
import {connect} from 'react-redux';
import {closeDrawer, push} from '../services/NavigationService';

class Sidebar extends Component {
  navigate(route) {
    closeDrawer(); //close drawer
    if (route === 'login') {
      clearAll(); //clear all shared Pref data
      this.props.clearMapData();

      setTimeout(() => {
        push(route);
      }, 800);
    } else {
      push(route);
    }
  }
  render() {
    const routes = [
      {
        title: 'All Leads',
        route: 'home',
      },
      {
        title: 'My Assigned Leads',
        route: 'myLeadTabs',
      },
      {
        title: 'My Appointments',
        route: 'myAppointment',
      },
      // {
      //     title: "Team Performance Report",
      //     route: 'reports'
      // },
      {
        title: 'Team Performance Report',
        route: 'reportsTableView',
      },
      {
        title: 'Commission Report',
        route: 'commsionReports',
      },
      {
        title: 'Calendar',
        route: 'calender',
      },
      {
        title: 'Training',
        route: 'training',
      },
      {
        title: 'Logout',
        route: 'login',
      },
    ];

    return (
      <Container>
        <ImageBackground style={{height: 200}} source={Images.drawer_bg_header}>
          <View
            style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
            <Image source={Images.logo} style={styles.headerIcon} />
          </View>
          <Image
            source={Images.bottom_logo}
            style={{
              width: 70,
              height: 50,
              alignSelf: 'flex-end',
              marginRight: 20,
              marginBottom: 10,
            }}
            resizeMode={'stretch'}
          />
        </ImageBackground>

        <FlatList
          data={routes}
          extraData={routes}
          renderItem={({item}) => (
            <ListItem noBorder button onPress={() => this.navigate(item.route)}>
              {/* <Image style={styles.iconDesign} source={item.icon} /> */}
              <Text style={styles.textDesign}>{item.title}</Text>
            </ListItem>
          )}
          keyExtractor={(item, index) => item.title + '-' + index}
        />
        <Text
          style={{
            width: 300,
            fontSize: 18,
            color: '#505261',
            textAlign: 'center',
            height: 40,
          }}>
          {`App Version:${DeviceInfo.getVersion()} Build ${DeviceInfo.getBuildNumber()}`}
        </Text>
      </Container>
    );
  }
}

DrawerContentTypes = {
  navigation: PropTypes.object,
};
const styles = StyleSheet.create({
  iconDesign: {
    width: 30,
    height: 30,
  },
  textDesign: {
    marginLeft: 25,
    fontSize: 18,
    color: '#505261',
  },
  headerIcon: {
    width: '60%',
    height: 100,
  },
});

const mapStateToPrps = (state) => {
  return {
    mapLeadList: state.leads.mapLeadList,
  };
};
export default connect(mapStateToPrps, {
  clearMapData,
})(Sidebar);
