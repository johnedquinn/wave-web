
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
    axios.post(url_users + '/login', { email: email, password: password })
    .then((resp) => {
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

    if (!query) query = {};
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

    // Check User Parameter
    if (!user) throw new Error('User not specified');
    if (!user.email || !user.name || !user.surname || !user.password) throw new Error('Missing user data');

    if (!user.img) user.img = '';

    // Initialize Params
    var params = {};
    params.token = token;

    // Make PUT Request
    axios.put(url_users + '/' + token, user, { params: params })
    .then((resp) => {
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

/// @func:  joinConversation
/// @param: token - NA
/// @param: convId - NA
/// @param: usrId - NA
/// @param: cb - NA
/// @desc:  NA
function joinConversation (token, convId, usrId, cb) {
    console.log('joinConversation(' + convId + ',' + usrId + ')');
    
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

    // Check Joined User ID Argument Exists
    if (!usrId) {
        if (cb) cb(new Error('Missing User ID'));
        return;
    }

    //
    var params = {};
    params.token = token;

    // 
    var data = {};
    data.user = usrId;

    //
    axios.post(url_conversations + '/' + convId + '/members', data, { params: params }) .then((resp) => {
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

/// @func:  leaveConversation
/// @param: token - NA
/// @param: convId - NA
/// @param: cb - NA
/// @desc:  NA
function leaveConversation (token, convId, cb) {
    console.log('leaveConversation(' + convId + ')');
    
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

    //
    var params = {};
    params.token = token;

    //
    axios.delete(url_conversations + '/' + convId + '/members', { params: params })
    .then((resp) => {
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
}

/// @func:  addMessage
/// @param: token - NA
/// @param: convId - NA
/// @param: content - NA
/// @param: cb - NA
/// @desc:  NA
function addMessage (token, convId, content, cb) {
    console.log('addMessage(' + convId + ')');
    
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

    // Check Content Argument Exists
    if (!content) {
        if (cb) cb(new Error('Missing Message Content'));
        return;
    }

    // Create New User Variable
    var newMsg = {
        ts: Date.now(),
        author: token,
        content: content
    }
    newMsg.id = String(Date.now());

    var query = {};
    query.token = token;

    axios.post(url_conversations + '/' + convId + '/messages', newMsg, { params: query })
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
}

/// @func:  removeMessage
/// @param: token - NA
/// @param: convId - NA
/// @param: msgId - NA
/// @param: cb - NA
/// @desc:  NA
function removeMessage (token, convId, msgId, cb) {
    console.log('removeMessage(' + convId + ',' + msgId + ')');
    
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

    // Check Message ID Argument Exists
    if (!msgId) {
        if (cb) cb(new Error('Missing Message ID'));
        return;
    }

    // Initialize Params Object
    var params = {};
    params.token = token;

    // Make Delete Request
    axios.delete(url_conversations + '/' + convId + '/messages/' + msgId, { params: params })
    .then((resp) => {
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
}

/// @func:  listMembers
/// @param: token - NA
/// @param: convId - NA
/// @param: ini - NA
/// @param: end - NA
/// @param: cb - NA
/// @desc:  NA
function listMembers (token, convId, cb) {
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

    axios.get(url_conversations + '/' + convId + '/members', { params: query })
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
}


export default {
    addUser,
    login,
    listUsers,
    updateUser,
    addConversation,
    updateConversation,
    listConversations,
    joinConversation,
    leaveConversation,
    addMessage,
    removeMessage,
    listMembers,
    listMessages
}