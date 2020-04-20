import Login from '../components/Login';
import {checkAuth, inputLoginChange} from '../Redux/actions'
import {connect} from 'react-redux';

const mapStateToProps = ({ login }) => {

  return({
    isLogged: login.isLogged
  })
};

const mapDispatchToProps = (dispatch) => ({
  handleChange: (e) => dispatch(inputLoginChange({[e.target.name]: e.target.value})),
  checkAuth: (e) => { e.preventDefault(); dispatch(checkAuth()) }
})
;

const login = connect(mapStateToProps, mapDispatchToProps)(Login);

export default login;
