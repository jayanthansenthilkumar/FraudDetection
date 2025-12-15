import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { supabase } from "@/integrations/supabase/client";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { MessageSquarePlus, Loader2, BookOpen, Brain, TrendingUp, Clock, MessageSquare, FileText } from "lucide-react";
import ChatHistory from "@/components/ChatHistory";
import { Session } from "@supabase/supabase-js";

const Dashboard = () => {
  const [session, setSession] = useState<Session | null>(null);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState({ 
    totalChats: 0, 
    todayChats: 0,
    totalNotes: 0,
    totalMaterials: 0
  });
  const navigate = useNavigate();
  const { toast } = useToast();

  useEffect(() => {
    // Get initial session
    supabase.auth.getSession().then(({ data: { session } }) => {
      setSession(session);
      setLoading(false);
      if (!session) {
        navigate("/auth");
      } else {
        fetchStats();
      }
    });

    // Listen for auth changes
    const { data: { subscription } } = supabase.auth.onAuthStateChange((_event, session) => {
      setSession(session);
      if (!session) {
        navigate("/auth");
      }
    });

    return () => subscription.unsubscribe();
  }, [navigate]);

  const fetchStats = async () => {
    try {
      // Fetch total chats
      const { count: totalCount } = await supabase
        .from("chat_sessions")
        .select("*", { count: "exact", head: true });

      // Fetch today's chats
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      const { count: todayCount } = await supabase
        .from("chat_sessions")
        .select("*", { count: "exact", head: true })
        .gte("created_at", today.toISOString());

      // Fetch notes count
      const { count: notesCount } = await supabase
        .from("notes")
        .select("*", { count: "exact", head: true });

      // Fetch materials count
      const { count: materialsCount } = await supabase
        .from("study_materials")
        .select("*", { count: "exact", head: true });

      setStats({
        totalChats: totalCount || 0,
        todayChats: todayCount || 0,
        totalNotes: notesCount || 0,
        totalMaterials: materialsCount || 0,
      });
    } catch (error) {
      console.error("Error fetching stats:", error);
    }
  };

  const startNewChat = () => {
    navigate("/chat");
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="flex-1 overflow-auto">
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-6xl mx-auto space-y-8">
          {/* Welcome Section */}
          <div className="space-y-2">
            <h2 className="text-3xl font-bold text-foreground">
              Welcome back! 👋
            </h2>
            <p className="text-muted-foreground text-lg">
              Ready to continue your learning journey?
            </p>
          </div>

          {/* Stats Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card className="border-l-4 border-l-primary">
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                  <MessageSquare className="h-4 w-4" />
                  Total Conversations
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold text-foreground">{stats.totalChats}</div>
                <p className="text-xs text-muted-foreground mt-1">All time</p>
              </CardContent>
            </Card>

            <Card className="border-l-4 border-l-blue-500">
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                  <Clock className="h-4 w-4" />
                  Today's Sessions
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold text-foreground">{stats.todayChats}</div>
                <p className="text-xs text-muted-foreground mt-1">Started today</p>
              </CardContent>
            </Card>

            <Card className="border-l-4 border-l-green-500">
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                  <FileText className="h-4 w-4" />
                  Notes
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold text-foreground">{stats.totalNotes}</div>
                <p className="text-xs text-muted-foreground mt-1">Saved notes</p>
              </CardContent>
            </Card>

            <Card className="border-l-4 border-l-orange-500">
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                  <BookOpen className="h-4 w-4" />
                  Study Materials
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold text-foreground">{stats.totalMaterials}</div>
                <p className="text-xs text-muted-foreground mt-1">Resources saved</p>
              </CardContent>
            </Card>
          </div>

          {/* Quick Actions */}
          <Card>
            <CardHeader>
              <CardTitle className="text-xl">Quick Actions</CardTitle>
              <CardDescription>
                Start a new conversation or continue where you left off
              </CardDescription>
            </CardHeader>
            <CardContent className="flex flex-wrap gap-3">
              <Button 
                onClick={startNewChat}
                size="lg"
                className="bg-gradient-to-r from-primary to-primary/80 hover:opacity-90"
              >
                <MessageSquarePlus className="mr-2 h-5 w-5" />
                Start New Conversation
              </Button>
              <Button 
                onClick={() => navigate("/notes")}
                size="lg"
                variant="outline"
              >
                <FileText className="mr-2 h-5 w-5" />
                Create Note
              </Button>
              <Button 
                onClick={() => navigate("/materials")}
                size="lg"
                variant="outline"
              >
                <BookOpen className="mr-2 h-5 w-5" />
                Add Material
              </Button>
            </CardContent>
          </Card>

          {/* Recent Activity */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Chat History */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <MessageSquare className="h-5 w-5" />
                  Recent Conversations
                </CardTitle>
                <CardDescription>
                  Your latest chat sessions
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ChatHistory />
              </CardContent>
            </Card>

            {/* Learning Insights */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <TrendingUp className="h-5 w-5" />
                  Learning Insights
                </CardTitle>
                <CardDescription>
                  Track your progress
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center justify-between p-4 rounded-lg bg-primary/5 border border-primary/10">
                  <div className="flex items-center gap-3">
                    <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                      <Brain className="h-6 w-6 text-primary" />
                    </div>
                    <div>
                      <p className="font-medium">Study Streak</p>
                      <p className="text-sm text-muted-foreground">
                        {stats.todayChats > 0 ? "Active today!" : "Start learning"}
                      </p>
                    </div>
                  </div>
                  <div className="text-2xl font-bold text-primary">
                    {stats.todayChats > 0 ? "✓" : "—"}
                  </div>
                </div>

                <div className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="text-muted-foreground">Total Activity</span>
                    <span className="font-medium">{stats.totalChats + stats.totalNotes} items</span>
                  </div>
                  <div className="h-2 bg-secondary rounded-full overflow-hidden">
                    <div 
                      className="h-full bg-gradient-to-r from-primary to-primary/60 transition-all"
                      style={{ width: `${Math.min((stats.totalChats + stats.totalNotes) * 2, 100)}%` }}
                    />
                  </div>
                </div>

                <Button 
                  variant="outline" 
                  className="w-full"
                  onClick={() => navigate("/analytics")}
                >
                  View Detailed Analytics
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
