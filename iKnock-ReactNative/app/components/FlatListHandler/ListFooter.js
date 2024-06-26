import React from "react";
import { View, ActivityIndicator } from "react-native";

const ListFooter = () => (
  <View
    style={{
      height: 80,
      alignItems: "center",
      justifyContent: "center"
    }}
  >
    <ActivityIndicator animating />
  </View>
);

export default ListFooter;
