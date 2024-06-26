import React from "react";
import { View, StyleSheet, Modal, Text } from "react-native";
import { Spinner } from 'native-base';

const SpinnerView = (props) => {
  return (
    <Modal
      animationType="none"
      onRequestClose={() => console.log("close")}
      supportedOrientations={["landscape", "portrait"]}
      transparent
      visible={true}>
      <View
        style={[styles.container, { backgroundColor: "rgba(0, 0, 0, 0.25)" }]}
        key={`spinner_${Date.now()}`}>
        <View style={styles.background}>

          <View style={{ width: 50, height: 50 }}>
            <Spinner
              style={styles.spinner}
              isVisible={true}
              color={"#fff"}
            />
          </View>
          <Text style={styles.textContent}>Loading...</Text>
        </View>
      </View>
    </Modal>
  );
};
const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: "transparent",
    position: "absolute",
    top: 0,
    bottom: 0,
    left: 0,
    right: 0
  },
  background: {
    backgroundColor: '#3c4145',
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'column',
    width: 140,
    height: 140,
  },
  spinner: {
    position: "relative",
    justifyContent: "center",
    alignItems: "center"
  },
  textContainer: {
    flex: 1,
    top: 0,
    bottom: 0,
    left: 0,
    right: 0,
    justifyContent: "center",
    alignItems: "center",
    position: "absolute"
  },
  textContent: {
    top: 15,
    height: 50,
    fontSize: 20,
    color: '#fff'
  }
});
export { SpinnerView };
