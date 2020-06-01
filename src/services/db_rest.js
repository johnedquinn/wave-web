
import axios from 'axios';

const url_base = 'http://localhost:9090';
const url_users = url_base + '/users.php';
const url_conversations = url_base + '/conversations.php';

/// @func:  addUser
/// @param: user - NA
/// @param: cb - NA
/// @desc:  NA
function addUser (user, cb) {
    console.log(`addUser(${JSON.stringify(user)})`);
    if (!user) throw new Error("User not specified");

    user.id = String(Date.now());

    axios.post(url_users, user) .then((resp) => {
        console.log('success: ' + JSON.stringify(resp.data));
        cb(null, resp.data); })
    .catch((err) => {
        if (err.response) {
            cb(new Error(err.response.status)); } else if (err.request) {
            cb(new Error('No response received'));
        } else {
            cb(err);
        }
    });
}

/// @func:  login
/// @param: email - NA
/// @param: password - NA
/// @param: cb - NA
/// @desc:  NA
function login(email, password, cb) {
    console.log(`login(${email},${password})`);
    if (!email) throw new Error('Email not specified');
    if (!password) throw new Error('Password not specified');
    axios.post(url_users + '/login', { email: email, password: password }) .then((resp) => {
        console.log('success: ' + JSON.stringify(resp.data));
        cb(null, resp.data.id, resp.data); })
    .catch((err) => {
        if (err.response) {
            cb(new Error(err.response.status));
        } else if (err.request) {
            cb(new Error('No response received'));
        } else {
            cb(err);
        }
    });
}

/// @func:  listUsers
/// @param: token - NA
/// @param: conv - NA
/// @param: cb - NA
/// @desc:  NA
function listUsers(token, query, cb) {
    console.log(`listUsers(${token},${JSON.stringify(query)})`);
    query.token = token;
    axios.get(url_users, { params: query })
    .then((resp) => {
        console.log('success: ' + JSON.stringify(resp.data)); cb(null, resp.data);
    }).catch((err) => {
        if (err.response) {
            cb(new Error(err.response.status));
        } else if (err.request) {
            cb(new Error('No response received'));
        } else {
            cb(err);
        }
    });
}

/// @TODO: REDO THIS
/// @func:  updateUser
/// @param: token - NA
/// @param: conv - NA
/// @param: cb - NA
/// @desc:  NA
function updateUser (token, user, cb) {
    console.log('updateUser(' + JSON.stringify(user) + ')');

    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check User Parameter
    if (!user) throw new Error('User not specified');
    if (!user.email || !user.name || !user.surname || !user.password) throw new Error('Missing user data');

    // Make Sure Unique Email
    for (var id in users) {
        if (users[id].email == user.email && users[id].id != token) {
            if (cb) cb(new Error('Email Already Taken'));
            return;
        }
    }

    // Set User Information
    users[token].email = user.email;
    users[token].name = user.name;
    users[token].surname = user.surname;
    users[token].img = user.img;

    if (cb) cb(null, users[token]);
}

/// @func:  addConversation
/// @param: token - NA
/// @param: conv - NA
/// @param: cb - NA
/// @desc:  NA
function addConversation (token, conv, cb) {
    console.log('addConversation(' + JSON.stringify(conv) + ')');

    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    /*var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }*/

    // Check Conversation Exists
    if (!conv) throw new Error('Conversation Not Specified');
    if (!conv.name || !conv.members) throw new Error('Missing Conversation Data');

    if (!conv.img) conv.img = '';

    // Create New Conversation Variable
    var newConv = {
        name: conv.name,
        img: conv.img,
        members: conv.members
    }
    newConv.messages = [];
    newConv.id = String(Date.now());
    newConv.token = token;

    // Add New Conversation to Conversations
    //conversations[newConv.id] = newConv;
    //conv.id = newConv.id;

    var params = {};
    params.token = token;

    //if (cb) cb(null, conv);
    axios.post(url_conversations, newConv, { params: params }) .then((resp) => {
        console.log('success: ' + JSON.stringify(resp.data));
        cb(null, resp.data); })
    .catch((err) => {
        if (err.response) {
            cb(new Error(err.response.status)); } else if (err.request) {
            cb(new Error('No response received'));
        } else {
            cb(err);
        }
    });
}

/// @func:  updateConversation
/// @param: token - NA
/// @param: conv - NA
/// @param: cb - NA
/// @desc:  NA
function updateConversation (token, conv, cb) {
    console.log('updateConversation(' + JSON.stringify(conv) + ')');
    console.log('Conversation Data: ' + JSON.stringify(conv));

    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Conv Argument Exists
    if (!conv) throw new Error('Conversation Not Specified');

    // Check Conv Attributes
    if (!conv.name || !conv.messages) throw new Error('Missing Conversation Data');

    if (!conv.img) conv.img = '';

    var params = {};
    params.token = token;

    axios.post(url_conversations + '/' + conv.id, conv) .then((resp) => {
        console.log('success: ' + JSON.stringify(resp.data));
        cb(null, resp.data); })
    .catch((err) => {
        if (err.response) {
            cb(new Error(err.response.status)); } else if (err.request) {
            cb(new Error('No response received'));
        } else {
            cb(err);
        }
    });

    // Check Valid Token
    /*var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }*/

    // Check Conversation Exists
    /*var convId = conversations[conv.id] ? conv.id : null;
    if (!convId) {
        if (cb) cb(new Error('No Conversation with Specified ID'));
        return;
    }*/

    // Check User is Member in Conversation
    /*var callerInConv = false;
    console.log('Members:');
    for (var member of conversations[convId]['members'])
        console.log(member);
        if (userId == member) callerInConv = true;
    if (!callerInConv) {
        if (cb) cb(new Error('Caller Not in Conversation'));
        return;
    }*/

    // Set Conv Information
    /*conversations[convId].name = conv.name;
    conversations[convId].img = conv.img;
    conversations[convId].members = conv.members;
    conversations[convId].messages = conv.messages;*/

    //if (cb) cb(null, users[token]);
}

/// @func:  listConversations
/// @param: token - NA
/// @param: query - NA
/// @param: cb - NA
/// @desc:  NA
function listConversations (token, query, cb) {
    console.log('listConversations()');

    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    query.token = token;
    console.log("QUERY: " + JSON.stringify(query));

    axios.get(url_conversations, { params: query })
    .then((resp) => {
        console.log('success: ' + JSON.stringify(resp.data));
        cb(null, resp.data);
    }).catch((err) => {
        if (err.response) {
            cb(new Error(err.response.status));
        } else if (err.request) {
            cb(new Error('No response received'));
        } else {
            cb(err);
        }
    });

    // Check Valid Token
    /*var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }*/

    //
    /*var results = [];
    for (var id in conversations) {
        var matches = !query || !Object.keys(query).length;
        if (!matches) {
            for (var cond in query) {
                if (String(conversations[id][cond]).indexOf(String(query[cond])) != -1) {
                    matches = true;
                    break;
                }
            }
        }
        if (matches) results.push({
            id: conversations[id].id,
            name: conversations[id].name,
            img: conversations[id].img,
            members: conversations[id].members,
            messages: conversations[id].messages
        });
    }

    if (cb) cb(null, results);*/
}

/// @func:  listMessages
/// @param: token - NA
/// @param: convId - NA
/// @param: ini - NA
/// @param: end - NA
/// @param: cb - NA
/// @desc:  NA
function listMessages (token, convId, ini, end, cb) {
    console.log('listMessages(' + convId + ')');
    
    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Conversation ID Argument Exists
    if (!convId) {
        if (cb) cb(new Error('Missing Conversation ID'));
        return;
    }

    var query = {};
    query.token = token;

    axios.get(url_conversations + '/' + convId + '/messages', { params: query })
    .then((resp) => {
        console.log('success: ' + JSON.stringify(resp.data));
        cb(null, resp.data);
    }).catch((err) => {
        if (err.response) {
            cb(new Error(err.response.status));
        } else if (err.request) {
            cb(new Error('No response received'));
        } else {
            cb(err);
        }
    });

    // Check Valid Token
    /*var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }*/


    // Check Conversation Exists
    /*if (!conversations[convId]) {
        if (cb) cb(new Error('Conversation Does Not Exist'));
        return;
    }*/
    
    // Check Calling User is Member
    /*var userInMembers = false;
    for (id in conversations[convId].members) {
        if (id == token) userInMembers = true;
    }
    if (!userInMembers) {
        if (cb) cb(new Error('User Not in Conversation'));
        return;
    }*/

    //if (cb) cb(null, conversations[convId].messages.slice(ini, end));
}

export default {
    addUser,
    login,
    listUsers,
    updateUser,
    addConversation,
    updateConversation,
    listConversations,
    listMessages
}